<?php namespace Pep\Dropcogs;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Pep\Dropcogs\DropboxSession;
use Pep\Dropcogs\Discogs\Client as DiscogsClient;
use Pep\Dropcogs\Dropbox\Client as DropboxClient;
use Exception;
use Dropbox\WriteMode;

class File extends Model
{

    const READY = 'ready';
    const PARSING = 'parsing';
    const ERROR = 'error';
    const PARSED = 'parsed';

    protected $table = 'files';

    public static $mimeTypes = [
        'audio/basic',
        'audio/L24',
        'audio/mp4',
        'audio/mpeg',
        'audio/ogg',
        'audio/flac',
        'audio/opus',
        'audio/vorbis',
        'audio/vnd.rn-realaudio',
        'audio/vnd.wave',
        'audio/webm',
        'audio/example',
    ];

    public function user()
    {
        return $this->hasOne('Pep\Dropcogs\User');
    }

    public function parse(DropboxSession $session)
    {
        echo "Parsing $this->path\n";
        $this->parsing_state = self::PARSING;
        $this->save();
        include app_path() . '/../vendor/james-heinrich/getid3/getid3/getid3.php';
        include app_path() . '/../vendor/james-heinrich/getid3/getid3/write.php';

        $dropbox = new DropboxClient($session->user_id, $session->access_token);

        $client = new DiscogsClient(
            config('services.discogs.key'),
            config('services.discogs.secret'),
            config('services.discogs.access_token'),
            config('services.discogs.appName')
        );

        $pathParts = explode('/', $this->path);
        $folder = $pathParts[count($pathParts) - 2];

        $searchResults = $client->search($folder);

        if (count($searchResults) > 0) {
            $releaseInfo = $client->release($this->getReleaseId($searchResults));

            $path = $this->path;
            $similarityCheck = [];

            array_walk($releaseInfo['tracklist'], function ($track) use ($releaseInfo, $path, &$similarityCheck) {
                similar_text($path, $releaseInfo['title'] . '-' . $track['title'], $percentage);
                $similarityCheck[$percentage] = $track;
            });

            $track = $similarityCheck[max(array_keys($similarityCheck))];

            $tmpFile = tempnam(sys_get_temp_dir(), 'Dropcogs');
            $handle = fopen($tmpFile, 'w');
            $metadata = $dropbox->getFile($path, $handle);
            fclose($handle);

            $tagWriter = new \getid3_writetags;

            $tagWriter->filename = $tmpFile;
            $tagWriter->overwrite_tags = true;
            $tagWriter->tag_encoding = 'UTF-8';
            $tagWriter->tagformats = ['id3v2.3'];
            $tagWriter->remove_other_tags = true;

            $tagData = [];
            $name = $track['position'] . ' - ' . $track['title'];
            $tagData['title'] = [$track['title']];
            $tagData['artist'] = isset($track['artists']) ? array_column($track['artists'], 'name') : array_column($releaseInfo['artists'], 'name');
            $tagData['album'] = [$releaseInfo['title']];
            $tagData['year'] = [$releaseInfo['year']];
            $tagData['genre'] = $releaseInfo['genres'];
            $tagData['track'] = [$track['position']];

            $tagData['attached_picture'][0]['data'] = $client->downloadImage($releaseInfo['thumb']);
            $tagData['attached_picture'][0]['picturetypeid'] = 0x03;
            $tagData['attached_picture'][0]['description'] = $releaseInfo['title'];
            $tagData['attached_picture'][0]['mime'] = 'image/jpeg';

            if (array_key_exists('images', $releaseInfo)) {
                $tagData['attached_picture'][1]['data'] = $client->downloadImage($releaseInfo['images'][0]['resource_url']);
                $tagData['attached_picture'][1]['picturetypeid'] = 0x00;
                $tagData['attached_picture'][1]['description'] = $releaseInfo['title'];
                $tagData['attached_picture'][1]['mime'] = 'image/jpeg';
            }

            $tagWriter->tag_data = $tagData;
            $tagWriter->WriteTags();

            if (!empty($tagWriter->warnings) || !empty($tagWriter->errors)) {
                $this->parsing_state = self::ERROR;
                $this->save();
                throw new Exception(implode(',', $tagWriter->warnings) . '-' . implode(',', $tagWriter->errors));
            }

            $handle = fopen($tmpFile, 'r');

            $newPath = '/Dropcogs/v0.3/';
            $newPath .= $releaseInfo['styles'][0] . '/';
            $newPath .= implode(', ', $tagData['artist']) . '/';
            $newPath .= $releaseInfo['title'] . '/';
            $newPath = str_replace(' ', '-', $newPath);
            $newPath .= $name . '.' . pathinfo($metadata['path'], PATHINFO_EXTENSION);

            $dropbox->uploadFileChunked($newPath, WriteMode::force(), $handle);

            fclose($handle);

            echo "Uploaded: {$track['title']}, {$path}\n";
        }

        $this->parsing_state = self::PARSED;
        $this->save();
    }

    private function getReleaseId($searchResults)
    {
        foreach ($searchResults as $result) {
            if ($result['type'] === 'release') {
                return $result['id'];
            }
        }
    }

    public static function loadEntries(Folder $folder, $cb, $entries = [])
    {
        foreach ($entries as $entry) {
            $file = self::where('path', $entry[0])
                ->first();

            if (!$file) {
                $file = new self;
            }

            if (is_null($entry[1])) {
                $file->delete();
            } else if (!$entry[1]['is_dir'] && in_array($entry[1]['mime_type'], self::$mimeTypes)) {
                $file->folder_id = $folder->id;
                $file->parent_shared_folder_id = $entry[1]['parent_shared_folder_id'];
                $file->rev = $entry[1]['rev'];
                $file->thumb_exists = (bool) $entry[1]['thumb_exists'];
                $file->path = $entry[1]['path'];
                $file->is_dir = (bool) $entry[1]['is_dir'];
                $file->client_mtime = array_key_exists('client_mtime', $entry[1]) ? new Carbon($entry[1]['client_mtime']) : null;
                $file->icon = $entry[1]['icon'];
                $file->read_only = (bool) $entry[1]['read_only'];
                $file->bytes = (int) $entry[1]['bytes'];
                $file->modified = new Carbon($entry[1]['modified']);
                $file->size = $entry[1]['size'];
                $file->root = $entry[1]['root'];
                $file->mime_type = $entry[1]['mime_type'];
                $file->revision = (int) $entry[1]['revision'];

                $file->save();

                if ($cb) {
                    $cb($file);
                }
            }
        }
    }
}

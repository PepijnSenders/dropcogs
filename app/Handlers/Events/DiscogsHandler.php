<?php namespace Pep\Dropcogs\Handlers\Events;

use Pep\Dropcogs\Events\FilesReadyEvent;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Pep\Dropcogs\Discogs\Client as DiscogsClient;
use Pep\Dropcogs\Dropbox\Client as DropboxClient;
use Pep\Dropcogs\File;
use Exception;
use Dropbox\WriteMode;

class DiscogsHandler implements ShouldBeQueued {

	public function handle(FilesReadyEvent $event) {
		include app_path() . '/../vendor/james-heinrich/getid3/getid3/getid3.php';
		include app_path() . '/../vendor/james-heinrich/getid3/getid3/write.php';

		$user = $event->getUser();
		$session = $user->getDropboxSession();

		$dropbox = new DropboxClient($session->user_id, $session->access_token);

		$client = new DiscogsClient(
			config('services.discogs.key'),
			config('services.discogs.secret'),
			config('services.discogs.access_token'),
			config('services.discogs.appName')
		);

		$filesByFolder = File::filesByFolder();

		foreach ($filesByFolder as $folder => $files) {
			$searchResults = $client->search($folder);

			if (count($searchResults) > 0) {
				$releaseInfo = $client->release($this->getReleaseId($searchResults));

				foreach ($releaseInfo['tracklist'] as $track) {
					$tmpFile = tempnam(sys_get_temp_dir(), 'Dropcogs');

					$handle = fopen($tmpFile, 'w');

					// @TODO Best match
					foreach ($files as $index => $file) {
						$pathPieces = explode('/', $file->path);

						similar_text($pathPieces[count($pathPieces) - 1], $track['title'], $similarity);

						if ($similarity > 80) {
							$useFile = $file;
							unset($files[$index]);
						}
					}

					$metadata = $dropbox->getFile($useFile->path, $handle);

					fclose($handle);

					$tagWriter = new \getid3_writetags;

					$tagWriter->filename = $tmpFile;
					$tagWriter->overwrite_tags = true;
					$tagWriter->tag_encoding = 'UTF-8';
					$tagWriter->tagformats = ['id3v2.3'];
					$tagWriter->remove_other_tags = true;

					$tagData = [];
					$name = $track['position'] . ' - ' . $track['title'];
					$tagData['title'] = [$name];
					$tagData['artist'] = [isset($track['artist']) ? $track['artist'] : $this->getArtist($releaseInfo['artists'])];
					$tagData['album'] = [$releaseInfo['title']];
					$tagData['year'] = [$releaseInfo['year']];
					$tagData['genre'] = $releaseInfo['genres'];
					$tagData['track'] = [$track['position']];

					$tagData['attached_picture'][0]['data'] = $client->downloadImage($releaseInfo['thumb']);
					$tagData['attached_picture'][0]['picturetypeid'] = 0x03;
					$tagData['attached_picture'][0]['description'] = $releaseInfo['title'];
					$tagData['attached_picture'][0]['mime'] = 'image/jpeg';

					foreach ($releaseInfo['images'] as $image) {
						$tagData['attached_picture'][0]['data'] = $client->downloadImage($image['resource_url']);
						$tagData['attached_picture'][0]['picturetypeid'] = 0x00;
						$tagData['attached_picture'][0]['description'] = $releaseInfo['title'];
						$tagData['attached_picture'][0]['mime'] = 'image/jpeg';
					}

					$tagWriter->tag_data = $tagData;

					$tagWriter->WriteTags();

					if (!empty($tagWriter->warnings) || !empty($tagWriter->errors)) {
						throw Exception(implode(',', $tagWriter->warnings) . '-' . implode(',', $tagWriter->errors));
					}

					$handle = fopen($tmpFile, 'r');

					$path = '/Dropcogs/';
					$path .= $releaseInfo['genres'][0] . '/';
					$path .= $this->getArtist($releaseInfo['artists']) . '/';
					$path .= $releaseInfo['title'] . '/';
					// @TODO extension stuff
					$path .= $name . '.mp3';

					$dropbox->uploadFileChunked($path, WriteMode::force(), $handle);

					fclose($handle);

					echo "Uploaded: {$track['title']}, {$useFile->path}\n";
				}
			}
		}
	}

	private function getArtist($artists) {
		return implode(', ', array_column($artists, 'name'));
	}

	private function getReleaseId($searchResults) {
		foreach ($searchResults as $result) {
			if ($result['type'] === 'release') {
				return $result['id'];
			}
		}
	}

}

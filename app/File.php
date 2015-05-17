<?php namespace Pep\Dropcogs;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class File extends Model {

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

	public function user() {
		return $this->hasOne('Pep\Dropcogs\User');
	}

	public static function filesByFolder() {
		$files = self::get();
		$folders = [];

		foreach ($files as $file) {
			$pathParts = explode('/', $file->path);
			$identifier = $pathParts[count($pathParts) - 2];

			if (isset($folders[$identifier]) && is_array($folders[$identifier])) {
				array_push($folders[$identifier], $file);
			} else {
				$folders[$identifier] = [
				  $file,
			  ];
			}
		}

		return $folders;
	}

	public static function loadEntries(Folder $folder, $entries = [], $cb) {
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

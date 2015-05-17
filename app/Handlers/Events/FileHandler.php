<?php namespace Pep\Dropcogs\Handlers\Events;

use Pep\Dropcogs\Events\AnalyzeEvent;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Pep\Dropcogs\Dropbox\Client as DropboxClient;
use Pep\Dropcogs\File;
use Illuminate\Support\Facades\Log;
use Pep\Dropcogs\Events\FilesReadyEvent;

class FileHandler implements ShouldBeQueued {

	/**
	 * Handle the event.
	 *
	 * @param  AnalyzeEvent  $event
	 * @return void
	 */
	public function handle(AnalyzeEvent $event) {
		$user = $event->getUser();
		$session = $user->getDropboxSession();

		$dropbox = new DropboxClient($session->user_id, $session->access_token);

		$folders = $user->folders()
			->where('include', true)
			->get();

		foreach ($folders as $folder) {
			$delta = $dropbox->getDelta($folder->cursor, $folder->path);

			while (count($delta['entries']) > 0) {
				Log::info("Delta count:" . count($delta['entries']) . "\n");
				$folder->cursor = $delta['cursor'];

				File::loadEntries($folder, $delta['entries'], function($file) {
					Log::info("Loaded $file->path.\n");
				});

				$delta = $dropbox->getDelta($folder->cursor, $folder->path);
			}

			event(new FilesReadyEvent($user));

			$folder->save();
		}
	}

}

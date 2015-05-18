<?php namespace Pep\Dropcogs\Handlers\Events;

use Pep\Dropcogs\Events\FilesReadyEvent;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class DiscogsHandler implements ShouldBeQueued {

	public function handle(FilesReadyEvent $event) {
		$user = $event->getUser();

		$folders = $user->folders()
			->get();

		foreach ($folders as $folder) {
			$file = $folder->files()
				->where('parsed', false)
				->first();

			if ($file) {
				$file->parse($user->getDropboxSession());

				event(new FilesReadyEvent($user));

				continue;
			}
		}
	}

}

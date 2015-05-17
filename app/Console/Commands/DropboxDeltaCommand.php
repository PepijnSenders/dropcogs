<?php namespace Pep\Dropcogs\Console\Commands;

use Illuminate\Console\Command;
use Pep\Dropcogs\User;
use Pep\Dropcogs\File;
use Pep\Dropcogs\Folder;
use Pep\Dropcogs\Dropbox\Client as DropboxClient;
use Pep\Dropcogs\Events\AnalyzeEvent;

class DropboxDeltaCommand extends Command {

	protected $name = 'dropbox:delta';
	protected $description = 'Update user files according to Dropbox Delta.';

	public function fire() {
		$users = User::get();

		foreach ($users as $user) {
			$session = $user->getDropboxSession();

			event(new AnalyzeEvent($user));
		}
	}

}
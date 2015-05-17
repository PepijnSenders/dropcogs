<?php namespace Pep\Dropcogs\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

  protected $commands = [
    'Pep\Dropcogs\Console\Commands\DropboxDeltaCommand',
  ];

  protected function schedule(Schedule $schedule) {
    $schedule->command('dropbox:delta')
      ->everyFiveMinutes();
  }

}
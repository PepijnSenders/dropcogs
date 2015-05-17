<?php namespace Pep\Dropcogs\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

	protected $listen = [
		'Pep\Dropcogs\Events\AnalyzeEvent' => [
			'Pep\Dropcogs\Handlers\Events\FileHandler',
		],
		'Pep\Dropcogs\Events\FilesReadyEvent' => [
			'Pep\Dropcogs\Handlers\Events\DiscogsHandler',
		],
	];

}

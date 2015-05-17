<?php

return [

	'mailgun' => [
		'domain' => '',
		'secret' => '',
	],

	'mandrill' => [
		'secret' => '',
	],

	'ses' => [
		'key' => '',
		'secret' => '',
		'region' => 'us-east-1',
	],

	'stripe' => [
		'model'  => 'User',
		'secret' => '',
	],

	'dropbox' => [
		'key' => env('DROPBOX_KEY'),
		'secret' => env('DROPBOX_SECRET'),
		'redirect' => env('DROPBOX_REDIRECT'),
		'app' => env('DROPBOX_APP'),
	],

	'discogs' => [
		'key' => env('DISCOGS_KEY'),
		'secret' => env('DISCOGS_SECRET'),
		'access_token' => env('DISCOGS_ACCESS_TOKEN'),
		'appName' => env('DISCOGS_APP_NAME'),
	],

];
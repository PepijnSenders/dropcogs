<?php namespace Pep\Dropcogs\Discogs;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class Client {

  protected $client;

  public function __construct($key, $secret, $token, $appName = '') {
    $this->client = new GuzzleClient([
      'base_url' => 'https://api.discogs.com',
      'defaults' => [
        'headers' => [
          'User-Agent' => "$appName/0.1 +http://dropcogs.herokuapp.com",
          'Authorization' => "Discogs key=$key, secret=$secret",
        ],
      ],
    ]);
  }

  public function downloadImage($url) {
    $response = $this->client->get($url);

    return (string) $response->getBody();
  }

  public function search($query = '', $count = 5) {
    $identifier = self::cacheIdentifier('search', func_get_args());

    if (Cache::has($identifier)) {
      return Cache::get($identifier)['results'];
    }

    $result = $this->client->get('/database/search', [
      'query' => [
        'q' => $query,
        'per_page' => $count,
      ],
    ])->json();

    Cache::put($identifier, $result, Carbon::now()->addMinutes(30));

    return $result['results'];
  }

  public function release($releaseId) {
    $identifier = self::cacheIdentifier('release', func_get_args());

    if (Cache::has($identifier)) {
      return Cache::get($identifier);
    }

    $result = $this->client->get("/releases/$releaseId")->json();

    Cache::put($identifier, $result, Carbon::now()->addMinutes(30));

    return $result;
  }

  public static function cacheIdentifier($funcName, $args = []) {
    return $funcName . json_encode($args);
  }

}
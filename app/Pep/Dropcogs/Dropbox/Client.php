<?php namespace Pep\Dropcogs\Dropbox;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Dropbox\Client as DbxClient;
use Carbon\Carbon;

class Client
{

    protected $accessToken;
    protected $userId;
    protected $client;

    public function __construct($userId, $accessToken)
    {
        $this->userId = $userId;
        $this->accessToken = $accessToken;

        $this->client = new DbxClient($accessToken, Auth::getAppName());
    }

    public static function cacheIdentifier($funcName, $args = [])
    {
        return $funcName . json_encode($args);
    }

    public function __call($name, $arguments)
    {
        $identifier = self::cacheIdentifier('name', $arguments);

        if (Cache::has($identifier)) {
            return Cache::get($identifier);
        }

        try {
            $result = call_user_func_array([$this->client, $name], $arguments);

            Cache::put($identifier, $result, Carbon::now()->addDays(30));

            return $result;
        } catch (DbxException $e) {
            Log::info($e->getMessage());
        }
    }
}

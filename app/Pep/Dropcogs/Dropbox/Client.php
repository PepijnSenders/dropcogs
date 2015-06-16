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

    public function __call($name, $arguments)
    {
        try {
            $result = call_user_func_array([$this->client, $name], $arguments);

            return $result;
        } catch (DbxException $e) {
            Log::info($e->getMessage());
        }
    }
}

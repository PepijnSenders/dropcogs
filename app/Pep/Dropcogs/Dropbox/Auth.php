<?php namespace Pep\Dropcogs\Dropbox;

use Pep\Dropcogs\Dropbox\DropboxException;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Dropbox as Dbx;
use Dropbox\AppInfo as DbxAppInfo;
use Dropbox\WebAuth as DbxWebAuth;
use Dropbox\ArrayEntryStore as DbxArrayEntryStore;
use Dropbox\Exception as DbxException;
use Carbon\Carbon;

class Auth
{

    public static function getWebAuth()
    {
        $appInfo = DbxAppInfo::loadFromJson(config('services.dropbox'));
        return new DbxWebAuth(
            $appInfo,
            self::getAppName(),
            config('services.dropbox.redirect'),
            self::getCsrfTokenStore()
        );
    }

    public static function getAppName()
    {
        return config('services.dropbox.app') . '/1.0';
    }

    public static function getCsrfTokenStore()
    {
        $session = [
            'dropbox-auth-csrf-token' => Input::get('state', Session::get('dropbox-auth-csrf-token', Session::get('_token'))),
        ];

        return new DbxArrayEntryStore($session, 'dropbox-auth-csrf-token');
    }

    public static function start()
    {
        $authorizeUrl = self::getWebAuth()
            ->start();

        return $authorizeUrl;
    }

    public static function finish($params = [])
    {
        try {
            list($accessToken, $userId, $urlState) = self::getWebAuth()
                ->finish($params);
        } catch (DbxException $e) {
            throw new Exception($e->getMessage());
        }

        return [
            'accessToken' => $accessToken,
            'userId' => $userId,
            'urlState' => $urlState,
        ];
    }
}

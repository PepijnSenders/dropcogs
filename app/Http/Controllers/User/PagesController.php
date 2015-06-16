<?php namespace Pep\Dropcogs\Http\Controllers\User;

use Pep\Dropcogs\Http\Controllers\Controller;
use Pep\Dropcogs\Dropbox\Auth as DropboxAuth;
use Pep\Dropcogs\User;
use Pep\Dropcogs\DropboxSession;

class PagesController extends Controller
{

    public function login()
    {
        return view('pages.users.login')
            ->with('url', DropboxAuth::start())
            ->with('message', session('message'));
    }

    public function configure($path = '')
    {
        $user = DropboxSession::getUser();

        $client = User::getUserClient();
        $metadata = $client->getMetadataWithChildren("/$path");

        $pathPieces = array_filter(explode('/', $metadata['path']));

        $folders = $user
            ->folders()
            ->get();

        return view('pages.users.configure')
            ->with('metadata', $metadata)
            ->with('basePath', $pathPieces)
            ->with('folders', $folders)
            ->with('user', $user);
    }
}

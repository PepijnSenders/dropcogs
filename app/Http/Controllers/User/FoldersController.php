<?php namespace Pep\Dropcogs\Http\Controllers\User;

use Pep\Dropcogs\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Pep\Dropcogs\Folder;
use Pep\Dropcogs\User;
use Pep\Dropcogs\DropboxSession;
use Pep\Dropcogs\Dropbox\Client as DropboxClient;
use Carbon\Carbon;

class FoldersController extends Controller {

  public function add(Request $request) {
    $user = DropboxSession::getUser();
    $client = User::getUserClient();

    $folder = new Folder;

    $path = $request->input('path');
    $metadata = $client->getMetadata($path);

    $folder->user_id = $user->id;
    $folder->rev = $metadata['rev'];
    $folder->modified = new Carbon($metadata['modified']);
    $folder->path = $metadata['path'];
    $folder->icon = $metadata['icon'];
    $folder->include = true;

    $folder->save();

    $pieces = explode('/', $path);

    while (count($pieces)) {
      $piecedPath = implode('/', $pieces);

      $folder = Folder::where('path', $piecedPath)
        ->where('include', false)
        ->first();

      if ($folder) {
        $folder->delete();
      }

      array_pop($pieces);
    }

    $pieces = explode('/', $path);
    array_pop($pieces);

    return Redirect::route('pages.users.configure', [
      'path' => implode('/', $pieces),
    ]);
  }

  public function remove(Request $request) {
    $user = DropboxSession::getUser();
    $client = User::getUserClient();

    $path = $request->input('path');

    $folder = Folder::where('path', $path)
      ->first();

    if ($folder) {
      $folder->delete();
    } else {
      $folder = new Folder;

      $metadata = $client->getMetadata($path);

      $folder->user_id = $user->id;
      $folder->rev = $metadata['rev'];
      $folder->modified = new Carbon($metadata['modified']);
      $folder->path = $metadata['path'];
      $folder->icon = $metadata['icon'];
      $folder->include = false;

      $folder->save();
    }

    $pieces = explode('/', $path);
    array_pop($pieces);

    return Redirect::route('pages.users.configure', [
      'path' => implode('/', $pieces),
    ]);
  }

}
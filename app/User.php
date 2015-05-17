<?php namespace Pep\Dropcogs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Pep\Dropcogs\Dropbox\Client as DropboxClient;

class User extends Model  {

  protected $table = 'users';

  public function folders() {
    return $this->hasMany('Pep\Dropcogs\Folder');
  }

  public function files() {
    return $this->hasMany('Pep\Dropcogs\File');
  }

  public function dropboxSessions() {
    return $this->hasMany('Pep\Dropcogs\DropboxSession');
  }

  public function getDropboxSession() {
    return $this->dropboxSessions()
      ->orderBy('created_at', 'DESC')
      ->limit(1)
      ->first();
  }

  public static function getUserClient() {
    $session = Session::get('dropbox_session');

    $client = new DropboxClient($session->user_id, $session->access_token);

    return $client;
  }

}
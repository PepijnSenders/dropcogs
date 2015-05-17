<?php namespace Pep\Dropcogs;

use Illuminate\Database\Eloquent\Model;

class DropboxSession extends Model {

  public function user() {
    return $this->hasOne('Pep\\Dropcogs\\User');
  }

  public static function getUser() {
    $session = session('dropbox_session');

    return User::where('id', $session->user_id)
      ->first();
  }

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDropboxSessionsTable extends Migration {

  public function up() {
    Schema::create('dropbox_sessions', function(Blueprint $table) {
      $table->increments('id');

      $table->integer('user_id')->index();
      $table->string('access_token');
      $table->string('dropbox_id');
      $table->string('url_state')->nullable();

      $table->timestamps();
    });
  }

  public function down() {
    Schema::drop('dropbox_sessions');
  }

}
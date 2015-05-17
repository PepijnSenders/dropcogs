<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration {

  public function up() {
    Schema::create('files', function(Blueprint $table) {
      $table->increments('id');

      $table->integer('folder_id')->index();
      $table->string('parent_shared_folder_id');
      $table->string('rev');
      $table->boolean('thumb_exists');
      $table->string('path');
      $table->boolean('is_dir');
      $table->dateTime('client_mtime')->nullable();
      $table->string('icon');
      $table->boolean('read_only');
      $table->integer('bytes');
      $table->dateTime('modified');
      $table->string('size');
      $table->string('root');
      $table->string('mime_type');
      $table->integer('revision');

      $table->timestamps();
    });
  }

  public function down() {
    Schema::drop('files');
  }

}

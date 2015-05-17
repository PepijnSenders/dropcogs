<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFoldersTable extends Migration {

  public function up() {
    Schema::create('folders', function(Blueprint $table) {
      $table->increments('id');

      $table->integer('user_id');
      $table->string('rev');
      $table->dateTime('modified');
      $table->string('path');
      $table->string('icon');
      $table->string('cursor')->nullable();
      $table->boolean('include')->default(true);

      $table->timestamps();
    });
  }

  public function down() {
    Schema::drop('folders');
  }

}

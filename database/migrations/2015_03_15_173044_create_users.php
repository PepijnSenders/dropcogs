<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsers extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('users', function(Blueprint $table) {
      $table->increments('id');

      $table->dateTime('configured_at')->nullable();
      $table->integer('dropbox_id');

      $table->string('display_name');
      $table->string('first_name');
      $table->string('last_name');
      $table->string('familiar_name');
      $table->string('email');

      $table->string('country');
      $table->string('locale');
      $table->string('referral_link');

      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop('users');
  }

}

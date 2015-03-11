<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSettingValuesTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('setting_values', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('setting_id');
			$table->string('value');
			$table->unsignedInteger('user_id')->nullable();

			$table->foreign('setting_id')->references('id')->on('settings')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('setting_values');
	}

}

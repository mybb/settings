<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingValuesTable extends Migration {

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

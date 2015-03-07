<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveIsUserSettingFromSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('settings', function (Blueprint $table) {
			$table->dropColumn('is_user_setting');

			$table->unique(['setting_id', 'user_id']);
		});

		Schema::table('setting_values', function (Blueprint $table) {
			$table->unique(['setting_id', 'user_id'], 'setting_values_setting_id_user_id_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('settings', function (Blueprint $table) {
			$table->boolean('is_user_setting')->default(false);
		});

		Schema::table('setting_values', function (Blueprint $table) {
			$table->dropUnique('setting_values_setting_id_user_id_unique');
		});
	}

}

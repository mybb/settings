<?php
/**
 * Create settings table
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/settings
 * @copyright Copyright (c) 2015, MyBB Group
 * @license   http://www.mybb.com/licenses/bsd3 BSD-3
 * @link      http://www.mybb.com
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSettingsTable extends Migration
{

    /**
    * Run the migrations.
    *
    * @return void
    */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('package')->default('mybb/core');
            $table->boolean('is_user_setting')->default(false);

            $table->unique(['name', 'package']);
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::drop('settings');
    }
}

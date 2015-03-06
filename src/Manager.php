<?php
/**
 * Setting manager.
 *
 * @author MyBB Group
 * @version 2.0.0
 * @package mybb/settings
 * @copyright Copyright (c) 2014, MyBB Group
 * @license http://www.mybb.com/about/license GNU LESSER GENERAL PUBLIC LICENSE
 * @link http://www.mybb.com
 */

namespace MyBB\Settings;


class Manager extends \Illuminate\Support\Manager
{
	/**
	 * Get the default driver name.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return $this->app['config']->get('settings.store');
	}

	/**
	 * Create a database driver.
	 *
	 * @return DatabaseStore
	 */
	public function createDatabaseDriver()
	{
		$connection = $this->app['db']->connection();

		$table = $this->app['config']->get('settings.settings_table');
		$valuesTable = $this->app['config']->get('settings.setting_values_table');

		return new DatabaseStore($this->app->make('Illuminate\Contracts\Auth\Guard'), $connection, $table, $valuesTable);
	}
}

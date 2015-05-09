<?php
/**
 * Setting manager.
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/settings
 * @copyright Copyright (c) 2015, MyBB Group
 * @license   http://www.mybb.com/licenses/bsd3 BSD-3
 * @link      http://www.mybb.com
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
		return new DatabaseStore(
			$this->app->make('Illuminate\Contracts\Auth\Guard'),
			$this->app->make('MyBB\Settings\Models\Setting'),
			$this->app->make('MyBB\Settings\Models\SettingValue')
		);
	}

	/**
	 * Create a cache driver.
	 *
	 * @return CacheStore
	 */
	public function createCacheDriver()
	{
		$cache = $this->app->make('Illuminate\Contracts\Cache\Repository');
		$cacheName = $this->app['config']->get('settings.settings_cache_name');

		return new CacheStore(
			$this->app->make('Illuminate\Contracts\Auth\Guard'),
			$this->app->make('MyBB\Settings\Models\Setting'),
			$this->app->make('MyBB\Settings\Models\SettingValue'),
			$cache,
			$cacheName
		);
	}
}

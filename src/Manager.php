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

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Cache\Repository;
use MyBB\Settings\Models\Setting;
use MyBB\Settings\Models\SettingValue;

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
			$this->app->make(Guard::class),
			$this->app->make(Setting::class),
			$this->app->make(SettingValue::class)
		);
	}

	/**
	 * Create a cache driver.
	 *
	 * @return CacheStore
	 */
	public function createCacheDriver()
	{
		$cache = $this->app->make(Repository::class);
		$cacheName = $this->app['config']->get('settings.settings_cache_name');

		return new CacheStore(
			$this->app->make(Guard::class),
			$this->app->make(Setting::class),
			$this->app->make(SettingValue::class),
			$cache,
			$cacheName
		);
	}
}

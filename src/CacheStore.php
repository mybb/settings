<?php
/**
 * Setting store that caches the main settings, and loads user settings from the DB.
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/settings
 * @copyright Copyright (c) 2014, MyBB Group
 * @license   http://www.mybb.com/about/license GNU LESSER GENERAL PUBLIC LICENSE
 * @link      http://www.mybb.com
 */

namespace MyBB\Settings;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\ConnectionInterface;

class CacheStore extends DatabaseStore
{
	/**
	 * Cache system.
	 *
	 * @var Repository
	 */
	protected $_cache;
	/**
	 * The name of the cache to store settings in.
	 *
	 * @var string
	 */
	protected $_cacheName;

	/**
	 * @param Guard               $guard              Laravel guard instance, used to get user settings.
	 * @param ConnectionInterface $connection         Database connection to use to manage settings.
	 * @param string              $settingsTable      The name of the main settings table.
	 * @param string              $settingsValueTable The name of the setting values table.
	 * @param Repository          $cache              Cache repository for settings.
	 * @param string              $cacheName          The name of the cache to use.
	 */
	public function __construct(
		Guard $guard,
		ConnectionInterface $connection,
		$settingsTable = 'settings',
		$settingsValueTable = 'setting_values',
		Repository $cache,
		$cacheName = 'mybb.core.settings'
	) {
		parent::__construct($guard, $connection, $settingsTable, $settingsValueTable);
		$this->_cache = $cache;
		$this->_cacheName = $cacheName;
	}

	/**
	 * Flush all setting changes to the backing store.
	 *
	 * @param array $settings     The setting data to flush to the backing store.
	 * @param array $userSettings The user setting data to flush to the backing store.
	 * @param int   $userId       The ID of the user to save the user settings for.
	 *
	 * @return bool Whether the settings were flushed correctly.
	 */
	protected function flush(array $settings, array $userSettings, $userId = -1)
	{
		parent::flush($settings, $userSettings, $userId);

		$this->_cache->forget($this->_cacheName);
	}

	/**
	 * Load all settings into the setting store.
	 *
	 * @return array An array of all of the loaded settings.
	 */
	protected function loadSettings()
	{
		if(($settings = $this->_cache->get($this->_cacheName)) === null)
		{
			$settings = parent::loadSettings();

			$this->_cache->forever($this->_cacheName, $settings);
		}

		return $settings;
	}

	/**
	 * Load all of the user settings into the setting store.
	 *
	 * @param int $userId The ID of the user to load the user settings for.
	 *
	 * @return array An array of all of the loaded user settings.
	 */
	protected function loadUserSettings($userId = -1)
	{
		return parent::loadUserSettings($userId);
	}
}

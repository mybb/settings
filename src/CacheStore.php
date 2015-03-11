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
use MyBB\Settings\Models\Setting;
use MyBB\Settings\Models\SettingValue;

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
	 * @param Guard        $guard             Laravel guard instance, used to get user settings.
	 * @param Setting      $settingsModel     Settings model instance.
	 * @param SettingValue $settingValueModel Setting value model instance.
	 * @param Repository   $cache             Cache repository for settings.
	 * @param string       $cacheName         The name of the cache to use.
	 */
	public function __construct(
		Guard $guard,
		Setting $settingsModel,
		SettingValue $settingValueModel,
		Repository $cache,
		$cacheName = 'mybb.core.settings'
	) {
		parent::__construct($guard, $settingsModel, $settingValueModel);
		$this->_cache = $cache;
		$this->_cacheName = $cacheName;
	}

	/**
	 * Flush all setting changes to the backing store.
	 *
	 * @param int $userId The ID of the user to save the user settings for.
	 *
	 * @return bool Whether the settings were flushed correctly.
	 */
	protected function flush($userId = -1)
	{
		parent::flush($userId);

		$this->_cache->forget($this->_cacheName);
	}

	/**
	 * Load all settings into the setting store.
	 *
	 * @return array An array of all of the loaded settings.
	 */
	protected function loadSettings()
	{
		if (($settings = $this->_cache->get($this->_cacheName)) === null) {
			$settings = parent::loadSettings();

			$this->_cache->forever($this->_cacheName, $settings);
		}

		return $settings;
	}
}

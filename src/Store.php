<?php
/**
 * Abstract setting store, providing useful methods for other store implementations.
 *
 * @author MyBB Group
 * @version 2.0.0
 * @package mybb/settings
 * @copyright Copyright (c) 2014, MyBB Group
 * @license http://www.mybb.com/about/license GNU LESSER GENERAL PUBLIC LICENSE
 * @link http://www.mybb.com
 */

namespace MyBB\Settings;

use Illuminate\Contracts\Auth\Guard;

abstract class Store
{
	/**
	 * Laravel guard instance, used to get user ID for user settings.
	 * @var Guard
	 */
	protected $_guard;

	/**
	 * An array of the loaded settings.
	 *
	 * @var array
	 */
	protected $_settings = [];

	/**
	 * An array of the loaded user settings.
	 *
	 * @var array
	 */
	protected $_userSettings = [];

	/**
	 * Whether the settings have been loaded yet.
	 *
	 * @var bool
	 */
	protected $_hasLoaded = false;

	/**
	 * Whether the settings have been modified at all.
	 *
	 * @var bool
	 */
	protected $_modified = false;

	/**
	 * @param Guard $guard Laravel guard instance, used to get user settings.
	 */
	public function __construct(Guard $guard)
	{
		$this->_guard = $guard;
	}

	/**
	 * Get a setting value.
	 *
	 * @param string $key          The name of the setting.
	 * @param mixed  $defaultValue A default value to use if the setting does not exist. Defaults to null.
	 * @param bool    $useUserSettings Whether to take into account user settings. User settings have priority over main settings. Defaults to true.
	 * @param string $package The name of the package the setting belongs to. Defaults to 'mybb.core'.
	 *
	 * @return mixed The value of the setting.
	 */
	public function get($key, $defaultValue = null, $useUserSettings = true, $package = 'mybb.core')
	{
		$this->assertLoaded();

		if ($useUserSettings && array_has($this->_userSettings, $package . '.' . $key)) {
			return $this->getFromUserSettings($key, $defaultValue, $package);
		}

		return $this->getFromMainSettings($key, $defaultValue, $package);
	}

	/**
	 * Get a setting value from the main setting store.
	 *
	 * @param string $key          The name of the setting.
	 * @param mixed  $defaultValue A default value to use if the setting does not exist. Defaults to null.
	 * @param string $package The name of the package the setting belongs to. Defaults to 'mybb.core'.
	 *
	 * @return mixed The value of the setting.
	 */
	private function getFromMainSettings($key, $defaultValue = null, $package = 'mybb.core')
	{
		$val = array_get($this->_settings, $package . '.' . $key, $defaultValue);

		if ($val === null || $defaultValue === null) {
			return $val;
		}

		return settype($val, gettype($defaultValue));
	}

	/**
	 * Get a setting value from the user setting store.
	 *
	 * @param string $key          The name of the setting.
	 * @param mixed  $defaultValue A default value to use if the setting does not exist. Defaults to null.
	 * @param string $package The name of the package the setting belongs to. Defaults to 'mybb.core'.
	 *
	 * @return mixed The value of the setting.
	 */
	private function getFromUserSettings($key, $defaultValue = null, $package = 'mybb.core')
	{
		$val = array_get($this->_userSettings, $package . '.' . $key, $defaultValue);

		if ($val === null || $defaultValue === null) {
			return $val;
		}

		return settype($val, gettype($defaultValue));
	}

	/**
	 * Set a setting value.
	 *
	 * @param string|array $key    The name of the setting.
	 * @param mixed  $value  The value for the setting.
	 * @param bool    $useUserSettings Whether to set the setting as a user setting. Defaults to false.
	 *
	 * @param string $package The name of the package the setting belongs to. Defaults to 'mybb.core'.
	 *
	 * @return void
	 */
	public function set($key, $value, $useUserSettings = false, $package = 'mybb.core')
	{
		$this->assertLoaded();
		$this->_modified = true;

		if ($useUserSettings) {
			if (is_array($key)) {
				foreach ($key as $settingKey => $settingVal) {
					array_set($this->_userSettings, $package . '.' . $settingKey, $settingVal);
				}
			} else {
				array_set($this->_userSettings, $package . '.' . $key, $value);
			}
		} else {
			if (is_array($key)) {
				foreach ($key as $settingKey => $settingVal) {
					array_set($this->_settings, $package . '.' . $settingKey, $settingVal);
				}
			} else {
				array_set($this->_settings, $package . '.' . $key, $value);
			}
		}
	}

	/**
	 * Check if a setting exists.
	 *
	 * @param string       $key The name of the setting.
	 * @param string $package The name of the package the setting belongs to. Defaults to 'mybb.core'.
	 *
	 * @return bool Whether the setting exists.
	 */
	public function has($key, $package = 'mybb.core')
	{
		$this->assertLoaded();

		return array_has($this->_userSettings, $package . '.' . $key) || array_has($this->_settings, $package . '.' . $key);
	}

	/**
	 * Flush all setting changes to the backing store.
	 *
	 * @param array $settings The setting data to flush to the backing store.
	 * @param array $userSettings The user setting data to flush to the backing store.
	 * @param int $userId The ID of the user to save the user settings for.
	 *
	 * @return bool Whether the settings were flushed correctly.
	 */
	protected abstract function flush(array $settings, array $userSettings, $userId = -1);

	/**
	 * Load all settings into the setting store.
	 *
	 * @return array An array of all of the loaded settings.
	 */
	protected abstract function loadSettings();

	/**
	 * Load all of the user settings into the setting store.
	 *
	 * @param int $userId The ID of the user to load the user settings for.
	 *
	 * @return array An array of all of the loaded user settings.
	 */
	protected abstract function loadUserSettings($userId = -1);

	/**
	 * Save any changes to the settings.
	 *
	 * @return bool Whether the settings were saved correctly.
	 */
	public function save()
	{
		if ($this->_modified) {
			return $this->flush($this->_settings, $this->_userSettings);
		}

		return false;
	}

	/**
	 * Ensures settings have been loaded by the store. If not, they are loaded from the backend.
	 */
	protected function assertLoaded()
	{
		if (!$this->_hasLoaded) {
			$this->_settings = $this->loadSettings();
			$user = $this->_guard->user();

			if ($user !== null) {
				$this->_userSettings = $this->loadUserSettings($user->getAuthIdentifier());
			}

			$this->_hasLoaded = true;
		}
	}

	/**
	 * Get all settings.
	 *
	 * @return array The combined user and board settings as an array.
	 */
	public function all()
	{
		$this->assertLoaded();
		
		return array_merge($this->_settings, $this->_userSettings);
	}
}

<?php
/**
 * Abstract setting store, providing useful methods for other store implementations.
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

abstract class Store
{
	const DEFAULT_SETTING_KEY = 'default';
	const USER_SETTING_KEY    = 'user';

	/**
	 * Laravel guard instance, used to get user ID for user settings.
	 *
	 * @var Guard
	 */
	protected $guard;

	/**
	 * An array of the loaded settings.
	 *
	 * @var array
	 */
	protected $settings = [];

	/**
	 * Whether the settings have been loaded yet.
	 *
	 * @var bool
	 */
	protected $hasLoaded = false;
	/**
	 * Whether the settings have been modified at all.
	 *
	 * @var bool
	 */
	protected $modified = false;
	/**
	 * A list of modified settings.
	 *
	 * @var array
	 */
	protected $modifiedSettings = [
		self::DEFAULT_SETTING_KEY => [],
		self::USER_SETTING_KEY => [],
	];
	/**
	 * A list of created settings.
	 *
	 * @var array
	 */
	protected $createdSettings = [];
	/**
	 * A list of deleted settings.
	 *
	 * @var array
	 */
	protected $deletedSettings = [];

	/**
	 * @param Guard $guard Laravel guard instance, used to get user settings.
	 */
	public function __construct(Guard $guard)
	{
		$this->guard = $guard;
	}

	/**
	 * Get a setting value.
	 *
	 * @param string $key             The name of the setting.
	 * @param mixed  $defaultValue    A default value to use if the setting does not exist. Defaults to null.
	 * @param bool   $useUserSettings Whether to take into account user settings. User settings have priority over main
	 *                                settings. Defaults to true.
	 * @param string $package         The name of the package the setting belongs to. Defaults to 'mybb/core'.
	 *
	 * @return mixed The value of the setting.
	 */
	public function get($key, $defaultValue = null, $useUserSettings = true, $package = 'mybb/core')
	{
		$this->assertLoaded();

		$val = null;

		if(isset($this->settings[$package][$key]))
		{
			$setting = $this->settings[$package][$key];

			if($useUserSettings && isset($setting[static::USER_SETTING_KEY]))
			{
				$val = $setting[static::USER_SETTING_KEY]['value'];
			}
			else
			{
				$val = $setting[static::DEFAULT_SETTING_KEY]['value'];
			}
		}

		return $this->determineValue($val, $defaultValue);
	}

	/**
	 * Determine the return value from an actual value and default value.
	 *
	 * @param mixed $value        The actual value.
	 * @param mixed $defaultValue The default value.
	 *
	 * @return mixed The determined value. If both $value and $defaultValue are not null, $value will be typecast to
	 *               the same type as $defaultValue.
	 */
	private function determineValue($value, $defaultValue)
	{
		if($value === null)
		{
			return $defaultValue;
		}

		if($defaultValue !== null)
		{
			settype($value, gettype($defaultValue));
		}

		return $value;
	}

	/**
	 * Set a setting value.
	 *
	 * @param string $key             The name of the setting.
	 * @param mixed  $value           The value for the setting.
	 * @param bool   $useUserSettings Whether to set the setting as a user setting. Defaults to false.
	 *
	 * @param string $package         The name of the package the setting belongs to. Defaults to 'mybb/core'.
	 *
	 * @return void
	 */
	public function set($key, $value, $useUserSettings = false, $package = 'mybb/core')
	{
		$this->assertLoaded();
		$this->modified = true;

		$settingType = ($useUserSettings === true) ? static::USER_SETTING_KEY : static::DEFAULT_SETTING_KEY;

		if(isset($this->settings[$package][$key][$settingType]))
		{ // Updating setting
			$this->settings[$package][$key][$settingType]['value'] = $value;

			$modifiedSettings[$this->settings[$package][$key][$settingType]['id']] = $this->settings[$package][$key][$settingType];
		}
		else
		{ // Creating setting
			$this->settings[$package][$key][$settingType] = [
				'package' => $package,
				'name' => $key,
				'value' => $value,
			];

			$this->createdSettings[$settingType] = $this->settings[$package][$key][$settingType];
		}
	}

	/**
	 * Check if a setting exists.
	 *
	 * @param string $key     The name of the setting.
	 * @param string $package The name of the package the setting belongs to. Defaults to 'mybb/core'.
	 *
	 * @return bool Whether the setting exists.
	 */
	public function has($key, $package = 'mybb/core')
	{
		$this->assertLoaded();

		return isset($this->settings[$package][$key]);
	}

	/**
	 * Flush all setting changes to the backing store.
	 *
	 * @param int $userId The ID of the user to save the user settings for.
	 *
	 * @return bool Whether the settings were flushed correctly.
	 */
	protected abstract function flush($userId = -1);

	/**
	 * Load all settings into the setting store.
	 *
	 * @return array An array of all of the loaded settings.
	 */
	protected abstract function loadSettings();

	/**
	 * Save any changes to the settings.
	 *
	 * @return bool Whether the settings were saved correctly.
	 */
	public function save()
	{
		if($this->modified)
		{
			$user = $this->guard->user();
			$userId = -1;

			if($user !== null)
			{
				$userId = $user->getAuthIdentifier();
			}

			return $this->flush($userId);
		}

		return false;
	}

	/**
	 * Ensures settings have been loaded by the store. If not, they are loaded from the backend.
	 */
	protected function assertLoaded()
	{
		if(!$this->hasLoaded)
		{
			$this->settings = $this->loadSettings();

			$this->hasLoaded = true;
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

		return array_merge($this->settings, $this->userSettings);
	}
}

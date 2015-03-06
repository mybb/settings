<?php
/**
 * Setting store contract.
 *
 * @author MyBB Group
 * @version 2.0.0
 * @package mybb/settings
 * @copyright Copyright (c) 2014, MyBB Group
 * @license http://www.mybb.com/about/license GNU LESSER GENERAL PUBLIC LICENSE
 * @link http://www.mybb.com
 */

namespace MyBB\Settings\Contracts;

use Illuminate\Contracts\Auth\Guard;

interface Store
{
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
	public function get($key, $defaultValue = null, $useUserSettings = true, $package = 'mybb.core');

	/**
	 * Check if a setting exists.
	 *
	 * @param string       $key The name of the setting.
	 * @param string $package The name of the package the setting belongs to. Defaults to 'mybb.core'.
	 *
	 * @return bool Whether the setting exists.
	 */
	public function has($key, $package = 'mybb.core');


	/**
	 * Save any changes to the settings.
	 *
	 * @return bool Whether the settings were saved correctly.
	 */
	public function save();
}

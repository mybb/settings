<?php
/**
 * Settings repository contract.
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/settings
 * @copyright Copyright (c) 2015, MyBB Group
 * @license   http://www.mybb.com/licenses/bsd3 BSD-3
 * @link      http://www.mybb.com
 */

namespace MyBB\Settings\Repositories;

interface SettingRepositoryInterface
{
	const USER_NONE = -1;

	/**
	 * Update a setting.
	 *
	 * @param array   $name    An array of setting/value to update. Eg: ['bbname' => 'MyBB Community'].
	 * @param integer $userId  The user to set the setting value for. Defaults to none (-1) to update a board setting.
	 * @param string  $package The package the setting belongs to.
	 *
	 * @return void
	 */
	public function update(array $name, $userId = SettingRepositoryInterface::USER_NONE, $package = 'mybb/core');

	/**
	 * Create a new setting.
	 *
	 * @param string|array $name    The name, or an array of names, of the setting(s) to create.
	 * @param string       $package The package the setting belongs to.
	 *
	 * @return void
	 */
	public function create($name, $package = 'mybb/core');

	/**
	 * Delete a setting.
	 *
	 * @param string|array $name    The name, or an array of names, of the setting(s) to delete.
	 * @param integer      $userId  The user to delete the setting value for. Defaults to none (-1) to delete a setting
	 *                              completely.
	 * @param string       $package The package the setting belongs to.
	 *
	 * @return boolean Whether the setting was deleted.
	 */
	public function delete($name, $userId = SettingRepositoryInterface::USER_NONE, $package = 'mybb/core');

	/**
	 * Check if a setting exists.
	 *
	 * @param string  $name    The name of the setting to check.
	 * @param integer $userId  The user to check if the setting exists for. Defaults to none (-1) to check if a setting
	 *                         exists at all.
	 * @param string  $package The package the setting belongs to.
	 *
	 * @return boolean Whether the setting exists.
	 */
	public function exists($name, $userId = SettingRepositoryInterface::USER_NONE, $package = 'mybb/core');
}

<?php
/**
 * Settings repository using Eloquent to query the database.
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/settings
 * @copyright Copyright (c) 2015, MyBB Group
 * @license   http://www.mybb.com/licenses/bsd3 BSD-3
 * @link      http://www.mybb.com
 */

namespace MyBB\Settings\Repositories\Eloquent;

use MyBB\Settings\Models\Setting;
use MyBB\Settings\Models\SettingValue;
use MyBB\Settings\Repositories\SettingRepositoryInterface;

class SettingRepository implements SettingRepositoryInterface
{
	/**
	 * @var Setting $settingsModel
	 */
	private $settingsModel;

	/**
	 * @var SettingValue $settingValuesModel
	 */
	private $settingValuesModel;

	public function __construct(Setting $settingsModel, SettingValue $settingValuesModel)
	{
		$this->settingsModel = $settingsModel;
		$this->settingValuesModel = $settingValuesModel;
	}

	/**
	 * Update a setting.
	 *
	 * @param array $name An array of setting/value to update. Eg: ['bbname' => 'MyBB Community'].
	 * @param integer $userId The user to set the setting value for. Defaults to none (-1) to update a board setting.
	 * @param string $package The package the setting belongs to.
	 *
	 * @return void
	 */
	public function update(array $name, $userId = SettingRepositoryInterface::USER_NONE, $package = 'mybb/core')
	{
		$user = $userId = (int) $userId;
		$package = (string) $package;

		if ($userId === static::USER_NONE) {
			$user = null;
		}

		if (empty($package)) {
			$package = 'mybb/core';
		}

		foreach ($name as $setting => $value) {
			$this->settingValuesModel->newQuery()
				->leftJoin('settings', 'settings.id', '=', 'setting_values.setting_id')
				->where('settings.name', $setting)
				->where('setting_values.user_id', $user)
				->where('settings.package', $package)
				->update(
					[
						'value' => $value,
					]
				);
		}
	}

	/**
	 * Create a new setting.
	 *
	 * @param string|array $name The name, or an array of names, of the setting(s) to create.
	 * @param string $package The package the setting belongs to.
	 *
	 * @return void
	 */
	public function create($name, $package = 'mybb/core')
	{
		if (!is_array($name)) {
			$name = [(string) $name];
		}

		if (empty($package)) {
			$package = 'mybb/core';
		}

		$insertArray = [];

		foreach ($name as $setting) {
			$insertArray[] = [
				'name' => $setting,
				'package' => $package,
			];
		}

		$this->settingsModel->newQuery()->insert($insertArray);
	}

	/**
	 * Delete a setting.
	 *
	 * @param string|array $name The name, or an array of names, of the setting(s) to delete.
	 * @param integer $userId The user to delete the setting value for. Defaults to none (-1) to delete a setting completely.
	 * @param string $package The package the setting belongs to.
	 *
	 * @return boolean Whether the setting was deleted.
	 */
	public function delete($name, $userId = SettingRepositoryInterface::USER_NONE, $package = 'mybb/core')
	{
		$userId = (int) $userId;
		$package = (string) $package;

		if (!is_array($name)) {
			$name = [(string) $name];
		}

		if (empty($package)) {
			$package = 'mybb/core';
		}

		if ($userId === static::USER_NONE) {
			return $this->settingsModel->newQuery()->whereIn('name', $name)->where('package', $package)->delete();
		} else {
			return $this->settingValuesModel->newQuery()
				->leftJoin('settings', 'settings.id', '=', 'setting_values.setting_id')
				->where('settings.name', $name)
				->where('setting_values.user_id', $userId)
				->where('settings.package', $package)
				->delete();
		}
	}

	/**
	 * Check if a setting exists.
	 *
	 * @param string $name The name of the setting to check.
	 * @param integer $userId The user to check if the setting exists for. Defaults to none (-1) to check if a setting exists at all.
	 * @param string $package The package the setting belongs to.
	 *
	 * @return boolean Whether the setting exists.
	 */
	public function exists($name, $userId = SettingRepositoryInterface::USER_NONE, $package = 'mybb/core')
	{
		$user = $userId = (int) $userId;

		if ($userId === static::USER_NONE) {
			$user = null;
		}

		$count = $this->settingValuesModel->newQuery()
			->leftJoin('settings', 'settings.id', '=', 'setting_values.setting_id')
			->where('settings.name', $name)
			->where('setting_values.user_id', $user)
			->where('settings.package', $package)
			->count();

		return ($count > 0);
	}
}
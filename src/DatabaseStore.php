<?php
/**
 * Database setting store.
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
use MyBB\Settings\Models\Setting;
use MyBB\Settings\Models\SettingValue;

class DatabaseStore extends Store
{
	/**
	 * Settings model.
	 *
	 * @var Setting $settingsModel
	 */
	private $settingsModel;
	/**
	 * Setting value model.
	 *
	 * @var SettingValue $settingValueModel
	 */
	private $settingValueModel;

	/**
	 * @param Guard        $guard Laravel guard instance, used to get user settings.
	 * @param Setting      $settingsModel Settings model instance.
	 * @param SettingValue $settingValueModel Setting value model instance.
	 */
	public function __construct(
		Guard $guard,
		Setting $settingsModel,
		SettingValue $settingValueModel
	) {
		parent::__construct($guard);

		$this->settingsModel = $settingsModel;
		$this->settingValueModel = $settingValueModel;
	}

	/**
	 * Load all settings into the setting store.
	 *
	 * @return array An array of all of the loaded settings.
	 */
	protected function loadSettings()
	{
		$settings = $this->settingsModel->newQuery()
			->leftJoin('setting_values', 'setting_values.setting_id', '=', 'settings.id')
			->select([
				'settings.id',
				'settings.name',
				'settings.package',
				'setting_values.value',
				'setting_values.user_id',
			]);

		if (($user = $this->guard->user()) !== null && $user->getAuthIdentifier() > 0) {
			$settings = $settings->where('user_id', '=', $user->getAuthIdentifier())->orWhereNull('user_id');
		} else {
			$settings = $settings->whereNull('user_id');
		}

		$settings = $settings->get();

		foreach ($settings as $setting) {
			$settingType = ($setting->user_id === null) ? Store::DEFAULT_SETTING_KEY : Store::USER_SETTING_KEY;

			$this->settings[$setting->package][$setting->name][$settingType] = [
				'id'      => $setting->id,
				'value'   => $setting->value,
				'package' => $setting->package,
				'name'    => $setting->name,
			];
		}

		return $this->settings;
	}
}

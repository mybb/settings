<?php
/**
 * Database setting store.
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
use Illuminate\Database\Query\JoinClause;
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
	 * @param Guard        $guard             Laravel guard instance, used to get user settings.
	 * @param Setting      $settingsModel     Settings model instance.
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
	 * Flush all setting changes to the backing store.
	 *
	 * @param int $userId The ID of the user to save the user settings for.
	 *
	 * @return bool Whether the settings were flushed correctly.
	 */
	protected function flush($userId = -1)
	{
		$this->handleUpdatedSettings();
		$this->handleCreatedSettings();
	}

	/**
	 * Load all settings into the setting store.
	 *
	 * @return array An array of all of the loaded settings.
	 */
	protected function loadSettings()
	{
		$settings = $this->settingsModel->join('setting_values', function (JoinClause $query)
		{
			$query->on('settings.id', '=', 'setting_values.setting_id');

			if(($user = $this->guard->user()) !== null && $user->getAuthIdentifier() > 0)
			{
				$query->where('user_id', '=', $user->getAuthIdentifier())->orWhereNull('user_id');
			}
			else
			{
				$query->whereNull('user_id');
			}
		})->get();

		foreach($settings as $setting)
		{
			$settingType = ($setting->user_id === null) ? Store::DEFAULT_SETTING_KEY : Store::USER_SETTING_KEY;

			$this->settings[$setting->package][$setting->name][$settingType] = [
				'id' => $setting->setting_id,
				'value' => $setting->value,
				'package' => $setting->package,
				'name' => $setting->name,
			];
		}

		return $this->settings;
	}

	private function handleUpdatedSettings()
	{
		foreach($this->modifiedSettings as $id => $setting)
		{
			$this->settingValueModel->where('setting_id', '=', $id)->update(['value' => $setting['value']]);
		}
	}

	private function handleCreatedSettings()
	{
		$this->createdSettings = array_unique($this->createdSettings);

		foreach($this->createdSettings[static::DEFAULT_SETTING_KEY] as $createdDefaultSetting)
		{
			$setting = $this->settingsModel->create([
				                                        'package' => $createdDefaultSetting['package'],
				                                        'name' => $createdDefaultSetting['name'],
			                                        ]);

			$setting->values()->create(['value' => $createdDefaultSetting['value']]);
		}

		if(($user = $this->guard->user()) !== null && $user->getAuthIdentifier() > 0)
		{
			foreach($this->createdSettings[static::USER_SETTING_KEY] as $createdDefaultSetting)
			{
				$setting = $this->settingsModel->create([
					                                        'package' => $createdDefaultSetting['package'],
					                                        'name' => $createdDefaultSetting['name'],
				                                        ]);

				$setting->values()->create([
					                           'value' => $createdDefaultSetting['value'],
					                           'user_id' => $user->getAuthIdentifier()
				                           ]);
			}
		}


	}
}

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
use MyBB\Settings\Repositories\SettingRepositoryInterface;

class DatabaseStore extends Store
{
    /**
     * @var SettingRepositoryInterface $settingsRepository
     */
    private $settingsRepository;

    /**
     * @param Guard $guard Laravel guard instance, used to get user settings.
     * @param SettingRepositoryInterface $settingsRepository Settings repository to retrieve setting values.
     */
    public function __construct(
        Guard $guard,
        SettingRepositoryInterface $settingsRepository
    ) {
        parent::__construct($guard);

        $this->settingsRepository = $settingsRepository;
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
        $this->handleCreatedSettings();
        $this->handleUpdatedSettings();
        $this->handleDeletedSettings();
    }

    /**
     * Load all settings into the setting store.
     *
     * @return array An array of all of the loaded settings.
     */
    protected function loadSettings()
    {
        $settings = $this->settingsRepository->getAllSettingsAndValues();

        foreach ($settings as $setting) {
            $settingType = ($setting->user_id === null) ? Store::DEFAULT_SETTING_KEY : Store::USER_SETTING_KEY;

            $this->settings[$setting->package][$setting->name][$settingType] = [
                'id' => $setting->id,
                'value' => $setting->value,
                'package' => $setting->package,
                'name' => $setting->name,
            ];
        }

        return $this->settings;
    }

    /**
     * Update existing settings with new setting values.
     */
    private function handleUpdatedSettings()
    {
        foreach ($this->modifiedSettings as $id => $setting) {
            if (is_numeric($id)) {
                $this->settingValueModel->where('setting_id', '=', $id)
                    ->where('user_id', '=', $setting['user_id'])
                    ->update(['value' => $setting['value']]);
            } else {
                if ($setting['id'] != -1) {
                    $this->settingValueModel->create([
                        'value' => $setting['value'],
                        'user_id' => $setting['user_id'],
                        'setting_id' => $setting['id']
                    ]);
                } else {
                    $foundSetting = $this->settingsModel->where('name', '=', $setting['name'])
                        ->where('package', '=', $setting['package'])->first();

                    if ($foundSetting != null) {
                        $foundSetting->values()->create([
                            'value' => $setting['value'],
                            'user_id' => $setting['user_id']
                        ]);
                    }
                }
            }

            unset($this->modifiedSettings[$id]);
        }
    }

    /**
     * Create any new settings that have been created.
     */
    private function handleCreatedSettings()
    {
        foreach ($this->createdSettings[static::DEFAULT_SETTING_KEY] as $key => $createdDefaultSetting) {
            $setting = $this->settingsModel->create([
                'package' => $createdDefaultSetting['package'],
                'name' => $createdDefaultSetting['name'],
            ]);

            $setting->values()->create(['value' => $createdDefaultSetting['value']]);

            unset($this->createdSettings[static::DEFAULT_SETTING_KEY][$key]);
        }

        if (($user = $this->guard->user()) !== null && $user->getAuthIdentifier() > 0) {
            foreach ($this->createdSettings[static::USER_SETTING_KEY] as $key => $createdDefaultSetting) {
                $setting = $this->settingsModel->create([
                    'package' => $createdDefaultSetting['package'],
                    'name' => $createdDefaultSetting['name'],
                ]);

                $setting->values()->create([
                    'value' => $createdDefaultSetting['value'],
                    'user_id' => $user->getAuthIdentifier()
                ]);

                unset($this->createdSettings[static::USER_SETTING_KEY][$key]);
            }
        }
    }

    /**
     * Remove any settings that have been deleted.
     */
    private function handleDeletedSettings()
    {
        foreach ($this->deletedSettings as $key => $setting) {
            if ($setting['just_user']) {
                if (($user = $this->guard->user()) !== null) {
                    $this->settingValueModel->join(
                        'settings',
                        'setting_values.setting_id',
                        '=',
                        'settings.id'
                    )->where('settings.name', '=', $setting['name'])->where(
                        'settings.package',
                        '=',
                        $setting['package']
                    )->where(
                        'setting_values.user_id',
                        '=',
                        $user->getAuthIdentifier()
                    )->delete();
                }
            } else {
                $settingEntry = $this->settingsModel->where('name', '=', $setting['name'])->where(
                    'package',
                    '=',
                    $setting['package']
                )->first();

                $settingEntry->values()->delete();
                $settingEntry->delete();
            }

            unset($this->deletedSettings[$key]);
        }
    }
}

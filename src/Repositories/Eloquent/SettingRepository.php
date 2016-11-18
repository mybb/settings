<?php
/**
 * Repository to load settings and values from the database using Eloquent.
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/settings
 * @copyright Copyright (c) 2015, MyBB Group
 * @license   http://www.mybb.com/licenses/bsd3 BSD-3
 * @link      http://www.mybb.com
 */

namespace MyBB\Settings\Repositories\Eloquent;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Collection;
use MyBB\Settings\Models\Setting;
use MyBB\Settings\Models\SettingValue;
use MyBB\Settings\Repositories\SettingRepositoryInterface;

class SettingRepository implements SettingRepositoryInterface
{
    /**
     * @var Guard $guard
     */
    private $guard;

    /**
     * @var Setting $settingsModel
     */
    private $settingsModel;

    /**
     * @var SettingValue $settingValueModel
     */
    private $settingValueModel;

    public function __construct(Guard $guard, Setting $settingsModel, SettingValue $settingValueModel)
    {
        $this->guard = $guard;
        $this->settingsModel = $settingsModel;
        $this->settingValueModel = $settingValueModel;
    }

    /**
     * Get all setting values, including user settings if a user is currently authenticated.
     *
     * @return Collection|static[]
     */
    public function getAllSettingsAndValues()
    {
        $settings = $this->settingsModel->leftJoin('setting_values', 'setting_values.setting_id', '=', 'settings.id')
            ->select([
                'settings.id',
                'settings.name',
                'settings.package',
                'setting_values.value',
                'setting_values.user_id'
            ]);

        if ($this->guard->check()) {
            $user = $this->guard->user();

            $settings = $settings->where('user_id', '=', $user->getAuthIdentifier())->orWhereNull('user_id');
        } else {
            $settings = $settings->whereNull('user_id');
        }

        return $settings->get();
    }
}

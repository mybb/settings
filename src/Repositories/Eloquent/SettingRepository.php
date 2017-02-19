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
use MyBB\Settings\Exceptions\InconsistencyOfSettingsException;
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

    /**
     * SettingRepository constructor.
     *
     * @param Guard $guard
     * @param Setting $settingsModel
     * @param SettingValue $settingValueModel
     */
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
                'settings.*',
                'setting_values.value',
                'setting_values.user_id',
            ]);

        if ($this->guard->check()) {
            $user = $this->guard->user();

            $settings = $settings->where('setting_values.user_id', '=', $user->getAuthIdentifier())
                ->orWhereNull('setting_values.user_id');
        } else {
            $settings = $settings->whereNull('setting_values.user_id');
        }

        return $settings->get();
    }

    /**
     * Creating settings croups from settings names
     *
     * @param array $skip        Settings groups to skip
     * @param array $forPackages Package names to limit results
     * @return Collection|static[]
     */
    public function getSettingsGroups(array $skip = [], array $forPackages = [])
    {
        $settings = $this->settingsModel->select('name', 'package');

        if (count($skip)) {
            foreach ($skip as $toSkip) {
                $settings->where('name', 'not like', $toSkip . '.%');
            }
        }

        if (count($forPackages)) {
            $settings->whereIn('package', $forPackages);
        }

        $settings = $settings->orderBy('id')->get();

        $groups = [];
        foreach ($settings as $setting) {
            $arrayOfName = explode('.', $setting['name']);
            $groups[] = [
                'group'   => $arrayOfName[0],
                'package' => [
                    'original'    => $setting['package'],
                    'dotNotation' => str_replace('/', '.', $setting['package']),
                    'split'       => explode('/', $setting['package']),
                ],
            ];
        }

        return collect($groups);
    }

    /**
     * Get settings for specified group of settings
     *
     * @param string $group   Name of group (first segment of setting name)
     * @param string $package Package name
     * @return mixed Collection|static[]
     */
    public function getSettingsForGroup(string $group, string $package = 'mybb/core')
    {
        return $this->settingsModel
            ->leftJoin('setting_values', 'setting_values.setting_id', '=', 'settings.id')
            ->select([
                'settings.*',
                'setting_values.value',
                'setting_values.user_id',
            ])
            ->where('settings.name', 'like', $group . '.%')
            ->where('package', $package)
            ->get();
    }

    /**
     * Update single setting by id
     *
     * @param int $settingId Setting Id
     * @param array $value   Setting value
     * @return mixed
     */
    public function update(int $settingId, $value, $userId = false)
    {
        if ($userId) {
            return $this->settingValueModel->updateOrCreate([
                'setting_id' => $settingId,
                'user_id'    => $userId,
            ], [
                'value' => $value,
            ]);
        }

        return $this->settingValueModel
            ->where('setting_id', $settingId)
            ->whereNull('user_id')
            ->update(['value' => $value]);
    }

    /**
     * Delete setting
     *
     * @param int $settingId Setting Id
     * @param bool $userId   User Id
     * @return mixed
     */
    public function delete(int $settingId, $userId = false)
    {
        $setting = $this->settingValueModel->where('setting_id', $settingId);
        if ($userId) {
            $setting->where('user_id', $userId);
        } else {
            $setting->whereNull('user_id');
        }
        return $setting->delete();
    }

    /**
     * Update bunch of settings
     *
     * @param array $settings Setting name => value pairs
     * @param bool $userId    User id for witch settings should be saved. Set false for global settings
     * @param string $package Package name
     * @return bool
     */
    public function updateSettings(array $settings = [], $userId = false, string $package = 'mybb/core')
    {
        $settingsIds = $this->getIdsForKeys(array_keys($settings), $package);

        foreach ($settings as $key => $value) {
            if ($userId) {
                if ($value == null) {
                    // restore default value for this setting for this user
                    $this->delete($settingsIds[$key]['id'], $userId);
                    continue;
                }
                $this->update($settingsIds[$key]['id'], $value, $userId);
            } else {
                $this->update($settingsIds[$key]['id'], $value);
            }
        }
        return true;
    }

    /**
     * Get settings id for given names
     *
     * @param array $keys     Settings names
     * @param string $package Package name
     * @return mixed Collection|static[] setting name => setting id pairs
     */
    public function getIdsForKeys(array $keys = [], string $package = 'mybb/core')
    {
        $ids = $this->settingsModel
            ->select('name', 'id')
            ->where('package', $package)
            ->whereIn('name', $keys)
            ->get()
            ->keyBy('name');

        if (count($ids) != count($keys)) {
            $missingSettings = [];
            foreach ($keys as $key) {
                if (!array_key_exists($key, $ids->toArray())) {
                    $missingSettings[] = $key;
                }
            }
            throw new InconsistencyOfSettingsException($missingSettings);
        }

        return $ids;
    }
}

<?php
/**
 * Repository interface to load settings and values.
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/settings
 * @copyright Copyright (c) 2015, MyBB Group
 * @license   http://www.mybb.com/licenses/bsd3 BSD-3
 * @link      http://www.mybb.com
 */

namespace MyBB\Settings\Repositories;

use Illuminate\Support\Collection;

interface SettingRepositoryInterface
{
    /**
     * Get all setting values, including user settings if a user is currently authenticated.
     *
     * @return Collection|static[]
     */
    public function getAllSettingsAndValues();

    /**
     * Creating settings croups from settings names
     *
     * @param array $skip        Settings groups to skip
     * @param array $forPackages Package names to limit results
     * @return Collection|static[]
     */
    public function getSettingsGroups(array $skip = [], array $forPackages = []);

    /**
     * Get settings for specified group of settings
     *
     * @param string $group   Name of group (first segment of setting name)
     * @param string $package Package name
     * @return mixed Collection|static[]
     */
    public function getSettingsForGroup(string $group, string $package = 'mybb.core');

    /**
     * Update single setting by id
     *
     * @param int $settingId Setting Id
     * @param array $value   Setting value
     * @return mixed
     */
    public function update(int $settingId, $value, $userId = false);

    /**
     * Delete setting
     *
     * @param int $settingId Setting Id
     * @param bool $userId   User Id
     * @return mixed
     */
    public function delete(int $settingId, $userId = false);

    /**
     * Update bunch of settings
     *
     * @param array $settings Setting name => value pairs
     * @param bool $userId    User id for witch settings should be saved. Set false for global settings
     * @param string $package Package name
     * @return bool
     */
    public function updateSettings(array $settings = [], $userId = false, string $package = 'mybb/core');

    /**
     * Get settings id for given names
     *
     * @param array $keys     Settings names
     * @param string $package Package name
     * @return mixed Collection|static[] setting name => setting id pairs
     */
    public function getIdsForKeys(array $keys = [], string $package = 'mybb/core');
}

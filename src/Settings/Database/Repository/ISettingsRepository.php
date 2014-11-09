<?php
/**
 * Settings repository contract.
 *
 * MyBB 2.0
 *
 * @copyright Copyright (c) 2014, MyBB Group
 * @license   http://www.mybb.com/about/license GNU LESSER GENERAL PUBLIC LICENSE
 * @link      http://www.mybb.com
 */

namespace MyBB\Settings\Database\Repository;

interface ISettingsRepository extends \ArrayAccess
{
    /**
     * Get all of the settings with their values.
     *
     * @return array All of the settings and associated values.
     */
    public function all();

    /**
     * Get a setting's value.
     *
     * @param string $name    The name of the setting to retrieve.
     * @param mixed  $default A default value to use if the setting does not exist. Defaults to null.
     * @param string $package The name of the package the setting belongs to. Defaults to 'mybb/core'.
     *
     * @return mixed The setting value.
     */
    public function getByName($name = '', $default = null, $package = 'mybb/core');

    /**
     * Set a collection of settings.
     *
     * @param array $settings An array of settings and values in the form array(package => array(name => value)).
     *
     * @return array A collection containing all of the updated settings.
     */
    public function setMany(array $settings);

    /**
     * Set a setting's value.
     *
     * @param string $name    The name of the setting to set.
     * @param mixed  $value   The value of the setting.
     * @param string $package The package to set the setting value for. Defaults to 'mybb/core'.
     *
     * @return mixed The updated setting.
     */
    public function set($name = '', $value, $package = 'mybb/core');

    /**
     * Delete a setting.
     *
     * @param string $name    The name of the setting to delete.
     * @param string $package The package to delete the setting for. Defaults to 'mybb/core'.
     *
     * @return bool Whether the setting was deleted.
     */
    public function delete($name = '', $package = 'mybb/core');

    /**
     * Delete all settings for a package.
     *
     * @param string $package The package name.
     *
     * @return bool Whether the settings were deleted.
     */
    public function deleteAllForPackage($package = 'mybb/core');

    /**
     * Add a setting.
     *
     * @param string $name    The name of the setting.
     * @param mixed  $value   The value of the setting.
     * @param string $package The package the setting belongs to.
     *
     * @return bool Whether the setting was added.
     */
    public function add($name = '', $value, $package = 'mybb/core');

    /**
     * Add a collection of settings.
     *
     * @param array $settings An array of settings and values in the form array(package => array(name => value)).
     *
     * @return bool Whether the settings were added.
     */
    public function addMany(array $settings);
} 

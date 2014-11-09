<?php
/**
 * Settings repository implementation using Laravel's Eloquent ORM.
 *
 * MyBB 2.0
 *
 * @copyright Copyright (c) 2014, MyBB Group
 * @license   http://www.mybb.com/about/license GNU LESSER GENERAL PUBLIC LICENSE
 * @link      http://www.mybb.com
 */

namespace MyBB\Settings\Database\Repository\Eloquent;

use MyBB\Settings\Database\Repository\ISettingsRepository;

class SettingsRepository implements ISettingsRepository
{
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    /**
     * Get all of the settings with their values.
     *
     * @return array All of the settings and associated values.
     */
    public function all()
    {
        // TODO: Implement all() method.
    }

    /**
     * Get a setting's value.
     *
     * @param string $name    The name of the setting to retrieve.
     * @param mixed  $default A default value to use if the setting does not exist. Defaults to null.
     * @param string $package The name of the package the setting belongs to. Defaults to 'mybb/core'.
     *
     * @return mixed The setting value.
     */
    public function getByName($name = '', $default = null, $package = 'mybb/core')
    {
        // TODO: Implement getByName() method.
    }

    /**
     * Set a collection of settings.
     *
     * @param array $settings An array of settings and values in the form array(package => array(name => value)).
     *
     * @return array A collection containing all of the updated settings.
     */
    public function setMany(array $settings)
    {
        // TODO: Implement setMany() method.
    }

    /**
     * Set a setting's value.
     *
     * @param string $name    The name of the setting to set.
     * @param mixed  $value   The value of the setting.
     * @param string $package The package to set the setting value for. Defaults to 'mybb/core'.
     *
     * @return mixed The updated setting.
     */
    public function set($name = '', $value, $package = 'mybb/core')
    {
        // TODO: Implement set() method.
    }

    /**
     * Delete a setting.
     *
     * @param string $name    The name of the setting to delete.
     * @param string $package The package to delete the setting for. Defaults to 'mybb/core'.
     *
     * @return bool Whether the setting was deleted.
     */
    public function delete($name = '', $package = 'mybb/core')
    {
        // TODO: Implement delete() method.
    }

    /**
     * Delete all settings for a package.
     *
     * @param string $package The package name.
     *
     * @return bool Whether the settings were deleted.
     */
    public function deleteAllForPackage($package = 'mybb/core')
    {
        // TODO: Implement deleteAllForPackage() method.
    }

    /**
     * Add a setting.
     *
     * @param string $name    The name of the setting.
     * @param mixed  $value   The value of the setting.
     * @param string $package The package the setting belongs to.
     *
     * @return bool Whether the setting was added.
     */
    public function add($name = '', $value, $package = 'mybb/core')
    {
        // TODO: Implement add() method.
    }

    /**
     * Add a collection of settings.
     *
     * @param array $settings An array of settings and values in the form array(package => array(name => value)).
     *
     * @return bool Whether the settings were added.
     */
    public function addMany(array $settings)
    {
        // TODO: Implement addMany() method.
}}

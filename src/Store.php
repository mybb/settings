<?php
/**
 * Abstract setting store, providing useful methods for other store implementations.
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
use MyBB\Settings\Repositories\SettingRepositoryInterface;

class Store implements \ArrayAccess
{
    const DEFAULT_SETTING_KEY = 'default';
    const USER_SETTING_KEY = 'user';

    /**
     * @var Guard
     */
    protected $guard;

    /**
     * @var SettingRepositoryInterface $settingRepository
     */
    protected $settingRepository;

    /**
     * An array of the loaded settings.
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Whether the settings have been loaded yet.
     *
     * @var boolean
     */
    protected $hasLoaded = false;

    /**
     * @param SettingRepositoryInterface $settingRepository Setting repository to load settings.
     * @param Guard $guard Laravel guard instance, used to get user settings.
     */
    public function __construct(SettingRepositoryInterface $settingRepository, Guard $guard)
    {
        $this->settingRepository = $settingRepository;
        $this->guard = $guard;
    }

    /**
     * Get a setting value.
     *
     * @param string $key             The name of the setting.
     * @param mixed  $defaultValue    A default value to use if the setting does not exist. Defaults to null.
     * @param bool   $useUserSettings Whether to take into account user settings. User settings have priority over main
     *                                settings. Defaults to true.
     * @param string $package         The name of the package the setting belongs to. Defaults to 'mybb/core'.
     *
     * @return mixed The value of the setting.
     */
    public function get($key, $defaultValue = null, $useUserSettings = true, $package = 'mybb/core')
    {
        $this->assertLoaded();

        $val = null;

        if (isset($this->settings[$package][$key])) {
            $setting = $this->settings[$package][$key];

            if ($useUserSettings && isset($setting[static::USER_SETTING_KEY])) {
                $val = $setting['value_' . static::USER_SETTING_KEY];
            } else {
                $val = $setting['value_' . static::DEFAULT_SETTING_KEY];
            }

            if (is_null($defaultValue)) {
                // If the default value supplied to this call is null, use the default from the store.
                $defaultValue = $setting['default_value'];
            }
        }

        return $this->determineValue($val, $defaultValue);
    }

    /**
     * Ensures settings have been loaded by the store. If not, they are loaded from the backend.
     */
    protected function assertLoaded()
    {
        if ($this->hasLoaded === false) {
            $settings = $this->settingRepository->getAllSettingsAndValues();

            foreach ($settings as $setting) {
                $settingType = static::DEFAULT_SETTING_KEY;

                if (!is_null($setting->user_id) && $setting->can_user_override) {
                    $settingType = static::USER_SETTING_KEY;
                }

                if (!is_null($setting->user_id) && !$setting->can_user_override) {
                    // User setting value for a setting that cannot be overriden, do not store it.
                    continue;
                }

                if (!isset($this->settings[$setting->package][$setting->name])) {
                    $this->settings[$setting->package][$setting->name] = [
                        'id' => (int)$setting->id,
                        'package' => $setting->package,
                        'name' => $setting->name,
                        'can_user_override' => $setting->can_user_override,
                        'setting_type' => $setting->setting_type,
                        'default_value' => $setting->default_value,
                    ];
                }

                // TODO: handle the `setting_type` column...
                $this->settings[$setting->package][$setting->name]['value_' . $settingType] = $setting->value;
            }

            $this->hasLoaded = true;
        }
    }

    /**
     * Determine the return value from an actual value and default value.
     *
     * @param mixed $value        The actual value.
     * @param mixed $defaultValue The default value.
     *
     * @return mixed The determined value. If both $value and $defaultValue are not null, $value will be typecast to
     *               the same type as $defaultValue.
     */
    private function determineValue($value, $defaultValue)
    {
        if ($value === null) {
            return $defaultValue;
        }

        if ($defaultValue !== null) {
            settype($value, gettype($defaultValue));
        }

        return $value;
    }

    /**
     * Check if a setting exists.
     *
     * @param string $key     The name of the setting.
     * @param string $package The name of the package the setting belongs to. Defaults to 'mybb/core'.
     *
     * @return bool Whether the setting exists.
     */
    public function has($key, $package = 'mybb/core')
    {
        $this->assertLoaded();

        return isset($this->settings[$package][$key]);
    }

    /**
     * Get all settings.
     *
     * @return array The combined user and board settings as an array.
     */
    public function all()
    {
        $this->assertLoaded();

        return $this->settings;
    }

    /**
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        $parts = explode('::', $offset);

        if (count($parts) >= 2) {
            $package = $parts[0];
            $key = $parts[1];
        } else {
            $package = 'mybb/core';
            $key = $parts[0];
        }

        return $this->has($key, $package);
    }

    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        $parts = explode('::', $offset);

        if (count($parts) >= 2) {
            $package = $parts[0];
            $key = $parts[1];
        } else {
            $package = 'mybb/core';
            $key = $parts[0];
        }

        return $this->get($key, null, true, $package);
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        // Do nothing
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        // Do nothing
    }
}

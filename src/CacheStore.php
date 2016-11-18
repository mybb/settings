<?php
/**
 * Setting store that caches the main settings, and loads user settings from the DB.
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
use Illuminate\Contracts\Cache\Repository;
use MyBB\Settings\Models\Setting;
use MyBB\Settings\Models\SettingValue;
use MyBB\Settings\Repositories\SettingRepositoryInterface;

class CacheStore extends DatabaseStore
{
    /**
     * Cache system.
     *
     * @var Repository
     */
    protected $cache;

    /**
     * The name of the cache to store settings in.
     *
     * @var string
     */
    protected $cacheName;

    /**
     * @param Guard        $guard             Laravel guard instance, used to get user settings.
     * @param SettingRepositoryInterface $settingsRepository Settings repository to retrieve setting values.
     * @param Repository   $cache             Cache repository for settings.
     * @param string       $cacheName         The name of the cache to use.
     */
    public function __construct(
        Guard $guard,
        SettingRepositoryInterface $settingsRepository,
        Repository $cache,
        $cacheName = 'mybb.core.settings'
    ) {
        parent::__construct($guard, $settingsRepository);
        $this->cache = $cache;
        $this->cacheName = $cacheName;
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
        parent::flush($userId);

        return $this->cache->forget($this->cacheName);
    }

    /**
     * Load all settings into the setting store.
     *
     * @return array An array of all of the loaded settings.
     */
    protected function loadSettings()
    {
        if (($settings = $this->cache->get($this->cacheName)) === null) {
            $settings = parent::loadSettings();

            $this->cache->forever($this->cacheName, $settings);
        }

        return $settings;
    }
}

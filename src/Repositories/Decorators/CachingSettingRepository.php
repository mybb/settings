<?php
/**
 * A setting repository that decorates another and adds caching.
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/settings
 * @copyright Copyright (c) 2015, MyBB Group
 * @license   http://www.mybb.com/licenses/bsd3 BSD-3
 * @link      http://www.mybb.com
 */

namespace MyBB\Settings\Repositories\Decorators;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use MyBB\Settings\Repositories\SettingRepositoryInterface;

class CachingSettingRepository implements SettingRepositoryInterface
{
    /**
     * @var Repository $cache
     */
    private $cache;

    /**
     * @var SettingRepositoryInterface $decoratedObject
     */
    private $decoratedObject;

    public function __construct(Repository $cache, SettingRepositoryInterface $decoratedObject)
    {
        $this->cache = $cache;
        $this->decoratedObject = $decoratedObject;
    }

    /**
     * @return SettingRepositoryInterface
     */
    public function getDecoratedObject()
    {
        return $this->decoratedObject;
    }

    /**
     * Get all setting values, including user settings if a user is currently authenticated.
     *
     * @return Collection|static[]
     */
    public function getAllSettingsAndValues()
    {
        return $this->cache->rememberForever('mybb/settings.allSettingsAndValues', function () {
            return $this->decoratedObject->getAllSettingsAndValues();
        });
    }
}

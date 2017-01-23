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
}

<?php
/**
 * Database setting store.
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/settings
 * @copyright Copyright (c) 2014, MyBB Group
 * @license   http://www.mybb.com/about/license GNU LESSER GENERAL PUBLIC LICENSE
 * @link      http://www.mybb.com
 */

namespace MyBB\Settings;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\JoinClause;

class DatabaseStore extends Store
{
	/**
	 * Database connection to use to load settings.
	 *
	 * @var ConnectionInterface $connection
	 */
	private $_connection;
	/**
	 * The name of the table to load settings from.
	 *
	 * @var string $_settingsTable
	 */
	private $_settingsTable;
	/**
	 * The name of the table to load setting values from.
	 *
	 * @var string $_settingsValueTable
	 */
	private $_settingsValueTable;

	/**
	 * @param Guard               $guard              Laravel guard instance, used to get user settings.
	 * @param ConnectionInterface $connection         Database connection to use to manage settings.
	 * @param string              $settingsTable      The name of the main settings table.
	 * @param string              $settingsValueTable The name of the setting values table.
	 */
	public function __construct(
		Guard $guard,
		ConnectionInterface $connection,
		$settingsTable = 'settings',
		$settingsValueTable = 'setting_values'
	) {
		parent::__construct($guard);

		$this->_connection = $connection;
		$this->_settingsTable = $settingsTable;
		$this->_settingsValueTable = $settingsValueTable;
	}

	/**
	 * Flush all setting changes to the backing store.
	 *
	 * @param array $settings     The setting data to flush to the backing store.
	 * @param array $userSettings The user setting data to flush to the backing store.
	 * @param int   $userId       The ID of the user to save the user settings for.
	 *
	 * @return bool Whether the settings were flushed correctly.
	 */
	protected function flush(array $settings, array $userSettings, $userId = -1)
	{
		if($userId > 0 && !empty($userSettings))
		{
			$this->flushUserSettings($userSettings, $userId);
		}

		if(!empty($settings))
		{
			$modifiedSettingNames = [];
			array_walk($settings, function ($val, $key) use (&$modifiedSettingNames)
			{
				$keyParts = explode('.', $key);
				array_shift($keyParts);
				$modifiedSettingNames[implode('.', $keyParts)] = $val;
			});

			$existingKeys = $this->_connection->table($this->_settingsTable)->where('is_user_setting', '=', false)
			                                  ->join($this->_settingsValueTable,
				                                  function (JoinClause $join) use ($userId)
				                                  {
					                                  $join->on($this->_settingsTable . '.id', '=',
					                                            $this->_settingsValueTable . '.setting_id');
				                                  })->whereIn('name', array_keys($modifiedSettingNames))
			                                  ->select([
				                                           $this->_settingsTable . '.package',
				                                           $this->_settingsTable . '.name',
				                                           $this->_settingsValueTable . '.value',
				                                           $this->_settingsTable . '.id'
			                                           ])->get();

			foreach($existingKeys as $existing)
			{
				if(isset($settings[$existing->package . '.' . $existing->name]))
				{
					if($settings[$existing->package . '.' . $existing->name] != $existing->value)
					{
						$this->_connection->table($this->_settingsValueTable)->where('setting_id', '=', $existing->id)
						                  ->update([
							                           'value' => $settings[$existing->package . '.' . $existing->name],
						                           ]);
					}

					unset($settings[$existing->package . '.' . $existing->name]);
				}
			}

			foreach($settings as $key => $val)
			{
				$keyParts = explode('.', $key);
				$package = $keyParts[0];
				array_shift($keyParts);
				$settingName = implode('.', $keyParts);

				$id = $this->_connection->table($this->_settingsTable)->insertGetId([
					                                                                    'name' => $settingName,
					                                                                    'package' => $package,
					                                                                    'is_user_setting' => false,
				                                                                    ]);

				$this->_connection->table($this->_settingsValueTable)->insert([
					                                                              'setting_id' => $id,
					                                                              'user_id' => null,
					                                                              'value' => $val,
				                                                              ]

				);
			}
		}
	}

	/**
	 * Flush the user settings to the database.
	 *
	 * @param array $userSettings The user settings to flush.
	 * @param int   $userId       The ID of the user to flush the settings for.
	 */
	private function flushUserSettings(array $userSettings, $userId = -1)
	{
		$modifiedSettingNames = [];
		array_walk($userSettings, function ($val, $key) use (&$modifiedSettingNames)
		{
			$keyParts = explode('.', $key);
			array_shift($keyParts);
			$modifiedSettingNames[implode('.', $keyParts)] = $val;
		});

		$existingKeys = $this->_connection->table($this->_settingsTable)->where('is_user_setting', '=', true)
		                                  ->join($this->_settingsValueTable, function (JoinClause $join) use ($userId)
		                                  {
			                                  $join->on($this->_settingsTable . '.id', '=',
			                                            $this->_settingsValueTable . '.setting_id')
			                                       ->where($this->_settingsValueTable . '.user_id', '=', $userId);
		                                  })->whereIn('name', array_keys($modifiedSettingNames))
		                                  ->select([
			                                           $this->_settingsTable . '.package',
			                                           $this->_settingsTable . '.name',
			                                           $this->_settingsValueTable . '.value',
			                                           $this->_settingsTable . '.id'
		                                           ])->get();

		foreach($existingKeys as $existing)
		{
			if(isset($userSettings[$existing->package . '.' . $existing->name]))
			{
				if($userSettings[$existing->package . '.' . $existing->name] != $existing->value)
				{
					$this->_connection->table($this->_settingsValueTable)->where('setting_id', '=', $existing->id)
					                  ->update([
						                           'value' => $userSettings[$existing->package . '.' . $existing->name],
					                           ]);
				}

				unset($userSettings[$existing->package . '.' . $existing->name]);
			}
		}

		foreach($userSettings as $key => $val)
		{
			$keyParts = explode('.', $key);
			$package = $keyParts[0];
			array_shift($keyParts);
			$settingName = implode('.', $keyParts);

			$id = $this->_connection->table($this->_settingsTable)->insertGetId([
				                                                                    'name' => $settingName,
				                                                                    'package' => $package,
				                                                                    'is_user_setting' => true,
			                                                                    ]);

			$this->_connection->table($this->_settingsValueTable)->insert([
				                                                              'setting_id' => $id,
				                                                              'user_id' => $userId,
				                                                              'value' => $val,
			                                                              ]

			);
		}
	}

	/**
	 * Load all settings into the setting store.
	 *
	 * @return array An array of all of the loaded settings.
	 */
	protected function loadSettings()
	{
		$settings = $this->_connection->table($this->_settingsTable)
		                              ->join($this->_settingsValueTable, function (JoinClause $join)
		                              {
			                              $join->on($this->_settingsTable . '.id', '=',
			                                        $this->_settingsValueTable . '.setting_id')->whereNull($this->_settingsValueTable . '.user_id');
		                              })
		                              ->select([
			                                       $this->_settingsTable . '.package',
			                                       $this->_settingsTable . '.name',
			                                       $this->_settingsValueTable . '.value',
		                                       ])
		                              ->get();


		foreach($settings as $setting)
		{
			array_set($this->_settings, $setting->package . '.' . $setting->name, $setting->value);
		}

		return $this->_settings;
	}

	/**
	 * Load all of the user settings into the setting store.
	 *
	 * @param int $userId The ID of the user to load the user settings for.
	 *
	 * @return array An array of all of the loaded user settings.
	 */
	protected function loadUserSettings($userId = -1)
	{
		if($userId > 0)
		{
			$settings = $this->_connection->table($this->_settingsTable)
			                              ->join($this->_settingsValueTable, function (JoinClause $join) use ($userId)
			                              {
				                              $join->on($this->_settingsTable . '.id', '=',
				                                        $this->_settingsValueTable . '.setting_id')
				                                   ->where($this->_settingsValueTable . '.user_id', '=', $userId);
			                              })
			                              ->select([
				                                       $this->_settingsTable . '.package',
				                                       $this->_settingsTable . '.name',
				                                       $this->_settingsValueTable . '.value'
			                                       ])
			                              ->get();

			foreach($settings as $setting)
			{
				array_set($this->_userSettings, $setting->package . '.' . $setting->name, $setting->value);
			}

			return $this->_userSettings;
		}

		return [];
	}
}

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
		if ($userId > 0) {
			$this->flushUserSettings($userSettings, $userId);
		}


	}

	/**
	 * Flush the user settings to the database.
	 *
	 * @param array $userSettings The user settings to flush.
	 * @param int   $userId The ID of the user to flush the settings for.
	 */
	private function flushUserSettings(array $userSettings, $userId = -1)
	{
		// TODO: Persist user settings
	}

	/**
	 * Load all settings into the setting store.
	 *
	 * @return array An array of all of the loaded settings.
	 */
	protected function loadSettings()
	{
		$settings = $this->_connection->table($this->_settingsTable)
		                              ->join($this->_settingsValueTable, $this->_settingsTable . '.id', '=',
		                                     $this->_settingsValueTable . '.setting_id')
		                              ->select([
			                                       $this->_settingsTable . '.package',
			                                       $this->_settingsTable . '.name',
			                                       $this->_settingsValueTable . '.setting_id',
			                                       $this->_settingsValueTable . '.value',
		                                       ])
		                              ->where('is_user_setting', '=', false)
		                              ->get();


		foreach ($settings as $setting) {
			array_set($this->_settings, $setting->package . '.' . $setting->name, ['package' => $setting->package, 'name' => $setting->name, 'setting_id' => $setting->setting_id, 'value' => $setting->value]);
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
		if ($userId > 0) {
			$settings = $this->_connection->table($this->_settingsTable)
			                              ->join($this->_settingsValueTable, function (JoinClause $join) use ($userId) {
				                              $join->on($this->_settingsTable . '.id', '=',
				                                        $this->_settingsValueTable . '.setting_id')
				                                   ->where($this->_settingsValueTable . '.user_id', '=', $userId)
				                                   ->where($this->_settingsTable . '.is_user_setting', '=', true);
			                              })
			                              ->select([
				                                       $this->_settingsTable . '.package',
				                                       $this->_settingsTable . '.name',
				                                       $this->_settingsValueTable . '.setting_id',
				                                       $this->_settingsValueTable . '.value'
			                                       ])
			                              ->get();

			foreach ($settings as $setting) {
				array_set($this->_userSettings, $setting->package . '.' . $setting->name, ['package' => $setting->package, 'name' => $setting->name, 'setting_id' => $setting->setting_id, 'value' => $setting->value]);
			}

			return $this->_userSettings;
		}
	}
}

<?php
/**
 * Setting model.
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/settings
 * @copyright Copyright (c) 2015, MyBB Group
 * @license   http://www.mybb.com/licenses/bsd3 BSD-3
 * @link      http://www.mybb.com
 */

namespace MyBB\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
	// @codingStandardsIgnoreStart

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var boolean
	 */
	public $timestamps = false;

	// @codingStandardsIgnoreStart

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'settings';
	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';
	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [];
	/**
	 * The relations to eager load on every query.
	 *
	 * @var array
	 */
	protected $with = [];

	public function values()
	{
		return $this->hasMany('MyBB\Settings\Models\SettingValue');
	}
}

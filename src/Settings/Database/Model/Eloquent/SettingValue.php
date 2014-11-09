<?php
/**
 * Setting value model for Laravel's Eloquent ORM.
 *
 * MyBB 2.0
 *
 * @copyright Copyright (c) 2014, MyBB Group
 * @license http://www.mybb.com/about/license GNU LESSER GENERAL PUBLIC LICENSE
 * @link http://www.mybb.com
 */

namespace MyBB\Settings\Database\Model\Eloquent;

use Illuminate\Database\Eloquent\Model;

class SettingValue extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'setting_values';
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = array();
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = array();
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array();
    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = array('setting');

    /**
     * One value belongs to one setting.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function setting()
    {
        return $this->belongsTo('MyBB\Settings\Database\Model\Eloquent\Setting');
    }
} 

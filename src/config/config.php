<?php
/**
 * Setting configuration file.
 */

return [
    /**
     * The source to load configuration from. Can be one of: database, files.
     */
    'setting_source' => 'database',
    /**
     * The name of configuration model.
     */
    'setting_model' => '\MyBB\Settings\Database\Model\Eloquent\Setting',
];

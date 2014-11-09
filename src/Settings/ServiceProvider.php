<?php
/**
 * Service provider for the MyBB\Settings package.
 *
 * MyBB 2.0
 *
 * @copyright Copyright (c) 2014, MyBB Group
 * @license http://www.mybb.com/about/license GNU LESSER GENERAL PUBLIC LICENSE
 * @link http://www.mybb.com
 */

namespace MyBB\Settings;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('mybb/settings', 'settings', __DIR__.'/../');
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app->make('config');

        switch ($config->get('settings.setting_source')) {
            case 'files':
                break;
            case 'database':
            default:
                break;
        }
    }

    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}

<?php
/**
 * Service provider for the MyBB\Settings package.
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/settings
 * @copyright Copyright (c) 2015, MyBB Group
 * @license   http://www.mybb.com/licenses/bsd3 BSD-3
 * @link      http://www.mybb.com
 */

namespace MyBB\Settings;

use Illuminate\Contracts\Foundation\Application;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var boolean
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes(
            [__DIR__ . '/../resources/config/settings.php' => config_path('settings.php')],
            'config'
        );
        $this->publishes(
            [__DIR__ . '/../resources/database/migrations/' => base_path('/database/migrations')],
            'migrations'
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            Repositories\SettingRepositoryInterface::class,
            Repositories\Eloquent\SettingRepository::class
        );

        $this->app->singleton(Manager::class, function (Application $app) {
            return new Manager($app);
        });

        $this->app->bind(Store::class, function (Application $app) {
            return $app->make(Manager::class)->driver();
        });

        $this->mergeConfigFrom(__DIR__ . '/../resources/config/settings.php', 'settings');
    }

    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {
        return [
            Manager::class,
            Store::class,
        ];
    }
}

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

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use MyBB\Settings\Repositories\Decorators\CachingSettingRepository;
use MyBB\Settings\Repositories\Eloquent\SettingRepository;
use MyBB\Settings\Repositories\SettingRepositoryInterface;

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
        $this->mergeConfigFrom(__DIR__ . '/../resources/config/settings.php', 'settings');

        $this->app->bind(SettingRepositoryInterface::class, function (Application $app) {
            /** @var Repository $config */
            $config = $app['config'];

            $dbRepository = $app->make(SettingRepository::class);

            if ($config['settings.store'] === 'cache') {
                return new CachingSettingRepository($app['cache'], $dbRepository);
            }

            return $dbRepository;
        });

        $this->app->singleton(Store::class);
    }

    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {
        return [Store::class];
    }
}

<?php
/**
 * Service provider for the MyBB\Settings package.
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/settings
 * @copyright Copyright (c) 2014, MyBB Group
 * @license   http://www.mybb.com/about/license GNU LESSER GENERAL PUBLIC LICENSE
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
		$this->publishes([__DIR__ . '/config/settings.php' => config_path('settings.php')], 'config');
		$this->publishes([__DIR__ . '/database/migrations/' => base_path('/database/migrations')], 'migrations');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bindShared('MyBB\Settings\Manager', function (Application $app) {

			return new Manager($app);
		});

		$this->app->bind('MyBB\Settings\Store', function (Application $app) {
			return $app->make('MyBB\Settings\Manager')->driver();
		});

		$this->mergeConfigFrom(__DIR__ . '/config/settings.php', 'settings');
	}

	/**
	 * Get the services provided.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'MyBB\Settings\Manager',
			'MyBB\Settings\Store',
		];
	}
}

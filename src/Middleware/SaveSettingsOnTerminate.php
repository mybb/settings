<?php
/**
 * Middleware to automatically save setting changes when a request completes.
 *
 * @author    MyBB Group
 * @version   2.0.0
 * @package   mybb/settings
 * @copyright Copyright (c) 2015, MyBB Group
 * @license   http://www.mybb.com/licenses/bsd3 BSD-3
 * @link      http://www.mybb.com
 */

namespace MyBB\Settings\Middleware;

use Closure;
use Illuminate\Contracts\Routing\TerminableMiddleware;
use MyBB\Settings\Store;

class SaveSettingsOnTerminate implements TerminableMiddleware
{
	/**
	 * @var Store $settings
	 */
	private $settings;

	/**
	 * @param Store $settings Settings store instance.
	 */
	public function __construct(Store $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * Perform any final actions for the request lifecycle.
	 *
	 * @param  \Symfony\Component\HttpFoundation\Request  $request
	 * @param  \Symfony\Component\HttpFoundation\Response $response
	 *
	 * @return void
	 */
	public function terminate($request, $response)
	{
		$this->settings->save();
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		return $next($request);
	}
}

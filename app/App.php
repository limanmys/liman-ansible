<?php
namespace App;

use App\Helpers\Route;

class App
{
	public function init()
	{
		$routes = include getPath('routes.php');

		foreach ($routes as $route => $destination) {
			Route::add($route, function () use ($destination) {
				$destination = explode('@', $destination);
				$class = 'App\\Controllers\\' . $destination[0];
				return (new $class())->{$destination[1]}();
			});
		}

		eval(Route::make());
	}
}

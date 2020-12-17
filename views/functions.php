<?php
require_once getPath('vendor/autoload.php');

use App\Helpers\Route;

$routes = include getPath('routes.php');

foreach ($routes as $route => $destination) {
	Route::add($route, function () use ($destination) {
		$destination = explode('@', $destination);
		$class = 'App\\Controllers\\' . $destination[0];
		return (new $class())->{$destination[1]}();
	});
}

eval(Route::make());

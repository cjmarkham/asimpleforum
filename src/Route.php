<?php

use Silex\Application;

class Route
{
	public static $app;

	public static function get($route)
	{
		if (strpos($route, '/') !== false)
		{
			list ($folder, $route) = explode('/', $route);
		}

		if (strpos($route, ':') !== false)
		{
			list ($controller, $method) = explode(':', $route);
		}
		else
		{
			$controller = $route;
			$method = 'index';
		}

		$controller = ucfirst($controller) . 'Controller';

		$admin = (isset($folder) && $folder == 'admin') ? true : false;

		if ($admin && !self::$app['auth']->admin)
		{
			return self::$app->redirect('/');
		}

		$path = (isset ($folder) ? 'Controller\\' . ucfirst($folder) . '\\' : 'Controller\\') . $controller;

		if (!class_exists($path))
		{
			throw new Exception($controller . ' does not exist in ' . $path);
		}

		$controller = new $path(self::$app);

		$params = func_get_args();
		array_shift($params);

		return call_user_func_array(array($controller, $method), $params);
	}
}
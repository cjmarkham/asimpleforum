<?php

use Silex\Application;

class Config
{
	public $config;
	public $base;

	public function load($file)
	{
		$path = dirname(__DIR__) . '/config/' . $this->base . '/' . $file . '.ini';

		if (file_exists($path))
		{
			$this->config = parse_ini_file($path, true);
		}
		else
		{
			throw new \Exception('No config file named ' . $path);
		}
	}

	public function get($key)
	{
		if (strpos($key, '.') !== false)
		{
			list($key, $value) = explode('.', $key);

			if (isset($this->config[$key]))
			{
				return $this->config[$key][$value];
			}
		}
	}
}
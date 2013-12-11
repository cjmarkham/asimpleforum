<?php

use Silex\Application;

class Config
{
	public $base;

	public function load () 
	{
		$dir = dirname(__DIR__) . '/config/' . $this->base;

		foreach (glob($dir . '/*.ini') as $config_file)
		{
			if (is_file($config_file))
			{
				$this->get($config_file);
			}
		}
	}

	public function get ($file)
	{
		$property = str_replace('.ini', '', basename($file));

		if (!file_exists($file))
		{
			die('No config file ' . $file);
		}

		$this->$property = parse_ini_file($file);
	}
}
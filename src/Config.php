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

		$config = parse_ini_file($file, true);

		foreach ($config as $key => $value)
		{
			if (is_array($value))
			{
				$array[$key] = array();

				foreach ($value as $k => $v)
				{
					$array[$key] = $value;
				}

				$this->$property = $array;
			}
			else
			{
				$this->$property = $config;
			}
		}
	}
}
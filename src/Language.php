<?php

use Silex\Application;

class Language
{
	public $app;
	public $file;

	public function __construct (Application $app)
	{
		$this->app = $app;

		$this->file = dirname(__DIR__) . '/public/languages/' . $this->app['config']->defaults['language'] . '.json';
		$this->phrases = json_decode(file_get_contents($this->file), true);
	}

	public function phrase ($phrase)
	{
		if (array_key_exists($phrase, $this->phrases))
		{
			return $this->phrases[$phrase];
		}

		return $phrase;
	}
}
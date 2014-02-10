<?php

namespace ASF;

use Silex\Application;

/**
 * Replaces the given string with its translated counterpart
 */
class Language
{
	/**
	 * @var object
	 */
	public $app;

	/**
	 * The file containing translated strings
	 * @var string
	 */
	public $file;

	/**
	 * The internal array of strings
	 * @var array
	 */
	public $phrases = array();

	/**
	 * Sets the app instance and loads the language file into an internal array
	 * @param Application $app [description]
	 */
	public function __construct (Application $app)
	{
		$this->app = $app;

		$this->file = __DIR__ . '/../../src/Languages/' . $this->app['defaults']['language'] . '.json';
		$this->phrases = json_decode(file_get_contents($this->file), true);
	}

	/**
	 * Replaces the string with its translation or returns the string
	 * if no translation exists
	 * @param  string $phrase       
	 * @param  array  $replacements An array of key value replacements
	 * @return string The translated string or original string
	 */
	public function phrase ($phrase, $replacements = array())
	{
		if (array_key_exists($phrase, $this->phrases))
		{
			$phrase = nl2br($this->phrases[$phrase]);
		}

		if (count($replacements) > 0)
		{
			$phrase = vsprintf($phrase, $replacements);
		}

		return $phrase;
	}
}
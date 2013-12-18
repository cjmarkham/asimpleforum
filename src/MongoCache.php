<?php

use Silex\Application;

class MongoCache
{
	public $app;

	public function __construct (Application $app)
	{
		$this->app = $app;
	}

	public function get (MongoCollection $collection, $key, \Closure $function = null)
	{
		$result = $collection->findOne(array(
			'key' => $key
		));

		if (empty($result) && $function instanceof \Closure)
		{
			$result = $function();
			$this->set($collection, $key, $result);
		}

		return $result;
	}

	public function set ($collection, $key, $data)
	{
		return $collection->insert(array(
			'key' => $key,
			'data' => $data['data']
		));
	}
}
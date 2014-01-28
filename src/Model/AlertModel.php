<?php

namespace Model;

class AlertModel 
{
	public $app;
	private $collection;

	public function __construct (\Silex\Application $app)
	{
		$this->app = $app;
		//$this->collection = $this->app['mongo']['default']->selectCollection($app['database']['name'], 'alerts');
	}

	public function findByDate ($timestamp = false)
	{
		if (!$timestamp)
		{
			return false;
		}

		$alerts = $this->app['db']->fetchAll('SELECT * FROM alerts WHERE starts <= ? AND (expires IS NULL OR expires > ?)', array(
			$timestamp,
			$timestamp
		));

		return $alerts;
	}

	public function sortByStart (array $alerts)
	{
		$_alerts = array();

		foreach ($alerts as $key => $alert)
		{
			$_alerts[date('dmY', $alert['starts'])][] = $alert;
		}

		return $_alerts;
	}
}
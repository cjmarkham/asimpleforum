<?php

namespace Controller;

use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{
	public function __construct(\Silex\Application $app)
	{
		$this->app = $app;
		$this->init();
	}

	public function index ()
	{

	}

	public function typeahead (Request $request)
	{
		$selection = $request->get('selection');
		$query = $request->get('query');

		$data = $this->app['search']->typeahead($query, $selection);

		return json_encode($data);
	}

	public function get ($query, $selection)
	{
		// Check if topic and redirect
		$topic = $this->app['topic']->find_by_name($query);

		if ($topic)
		{
			$url = '/' . urlencode($topic['forumName']) . '/' . urlencode($topic['name']) . '-' . $topic['id'] . '/1';
			return $this->app->redirect($url);
		}

		// if no topic then list matches
		$data = $this->app['search']->get($query, $selection);

		return $this->app['twig']->render('Search/get.twig', array(
			'title' 			=> 'Search - ' . $query,
			'section'			=> 'search',
			'data' 				=> $data,
			'query'				=> $query
		) + $this->extras);
	}

}
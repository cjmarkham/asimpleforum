<?php

namespace Controller;

class ForumController extends Controller
{
	public function __construct(\Silex\Application $app)
	{
		$this->app = $app;
		$this->init();
	}

	public function index($name, $forum_id, $page = 1)
	{
		$forum_id = (int) $forum_id;

		if (!$forum_id)
		{
			$this->app->redirect('/');
		}

		$forum = $this->app['forum']->find_by_id($forum_id);

		if (!$forum)
		{
			$this->app->redirect('/');
		}

		return $this->app['twig']->render('Forum/index.twig', array(
			'title' 			=> $forum['name'],
			'section'			=> 'forums',
			'forum'				=> $forum
		) + $this->extras);
	}
}
<?php

namespace Controller;

class ForumController extends BaseController
{
	public $app;

	public function index($name, $id)
	{
		$id = (int) $id;

		if (!$id)
		{
			$this->app->redirect('/');
		}

		$forum = $this->app['forum']->find_by_id($id);

		if (!$forum)
		{
			$this->app->redirect('/');
		}

		$topics = $this->app['topic']->find_by_forum($id);

		return $this->app['twig']->render('Forum/index.twig', array(
			'title' 			=> $forum['name'],
			'section'			=> 'forums',
			'forum'				=> $forum,
			'topics' 			=> $topics
		) + $this->params);
	}
}
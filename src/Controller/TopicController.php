<?php

namespace Controller;

class TopicController extends Controller
{
	public function __construct(\Silex\Application $app)
	{
		$this->app = $app;
		$this->init();
	}

	public function index ($topic_name, $topic_id, $page = 1)
	{
		$topic_id = (int) $topic_id;

		if (!$topic_id)
		{
			$this->app->redirect('/');
		}

		$topic = $this->app['topic']->findById($topic_id);
		if (!$topic)
		{
			$this->app->redirect('/');
		}

		$forum = $this->app['forum']->findById($topic['forum']);

		return $this->app['twig']->render('Topic/index.twig', [
			'title' 			=> $topic['name'],
			'section'			=> 'forums',
			'forum'				=> $forum,
			'topic'				=> $topic,
			'page'				=> $page
		] + $this->extras);
	}

	public function newest ()
	{
		$topics = $this->app['topic']->findRecent(10);

		return $this->app['twig']->render('Topic/newest.twig', [
			'title' 			=> 'Newest Topics',
			'section'			=> 'new-topics',
			'topics'			=> $topics
		] + $this->extras);
	}
}
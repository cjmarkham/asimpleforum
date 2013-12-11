<?php

namespace Controller;

class TopicController extends BaseController
{
	public $app;

	public function index ($topic_name, $topic_id)
	{
		$topic_id = (int) $topic_id;

		if (!$topic_id)
		{
			$this->app->redirect('/');
		}

		$topic = $this->app['topic']->find_by_id($topic_id);

		if (!$topic)
		{
			$this->app->redirect('/');
		}

		$posts = $this->app['post']->find_by_topic($topic_id);

		return $this->app['twig']->render('Topic/index.twig', array(
			'title' 			=> $topic['name'],
			'section'			=> 'topics',
			'topic'				=> $topic,
			'posts' 			=> $posts
		) + $this->params);
	}
}
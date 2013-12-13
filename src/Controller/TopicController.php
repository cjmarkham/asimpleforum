<?php

namespace Controller;

class TopicController extends BaseController
{
	public $app;

	public function index ($topic_name, $topic_id, $page = 1)
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

		$forum = $this->app['forum']->find_by_id($topic['forum']);

		$posts = $this->app['post']->find_by_topic($topic_id, (int) $page);

		if (empty($posts['data']))
		{
			return $this->app->redirect('/' . urlencode($forum['name']) . '/' . urlencode($topic['name']) . '-' . $topic_id . '/1');
		}

		return $this->app['twig']->render('Topic/index.twig', array(
			'title' 			=> $topic['name'],
			'section'			=> 'forums',
			'forum'				=> $forum,
			'topic'				=> $topic,
			'posts' 			=> $posts['data'],
			'pagination'		=> $posts['pagination']
		) + $this->params);
	}
}
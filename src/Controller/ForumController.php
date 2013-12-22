<?php

namespace Controller;

class ForumController
{
	public $app;

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

		$topics = $this->app['topic']->find_by_forum($forum_id, $page);

		if (empty($topics['data']) && $page > 1)
		{
			return $this->app->redirect('/' . urlencode($forum['name']) . '-' . $forum_id . '/1');
		}

		return $this->app['twig']->render('Forum/index.twig', array(
			'title' 			=> $forum['name'],
			'section'			=> 'forums',
			'forum'				=> $forum,
			'topics' 			=> $topics['data']['data'],
			'pagination'		=> $topics['pagination']
		));
	}
}
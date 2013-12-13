<?php

namespace Model;

class BaseModel
{
	public function pagination($total, $per_page = 10, $page = 1, $side = 5)
	{
		$pagination = array(
			'per_page' => $per_page,
			'page' => (int) $page,
			'sql_text' => ($page > 1) ? 'LIMIT ' . (($per_page * $page) - $per_page) . ', ' . $per_page : 'LIMIT ' . $per_page,
			'results' => (int) $total
		);

		$pagination['pages'] = array(
			'current' => (int) $page,
			'prev' => array(),
			'next' => array(),
			'total' => (int) ceil($total / $per_page)
		);

		if ($page <= $pagination['pages']['total'])
		{
			for ($i = $page - 1; $i >= $page - $side; $i--)
			{
				if (count($pagination['pages']['prev']) < $side && $i > 0)
				{
					$pagination['pages']['prev'][] = $i;
				}
			}
		}

		if ($pagination['pages']['prev'])
		{
			$pagination['pages']['prev'] = array_reverse($pagination['pages']['prev']);
		}

		if ($page < $pagination['pages']['total'])
		{
			for ($i = $page + 1; $i <= $pagination['pages']['total']; $i++)
			{
				if (count($pagination['pages']['next']) < $side)
				{
					$pagination['pages']['next'][] = $i;
				}
			}
		}

		return $pagination;
	}
}
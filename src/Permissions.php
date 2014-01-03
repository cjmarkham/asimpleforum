<?php

use Silex\Application;

class Permissions
{
	public static $app;

	const CREATE_POST = 1; // includes delete

	const CREATE_TOPIC = 2; // includes delete

	const EDIT_POSTS = 4; // includes delete

	const EDIT_TOPICS = 8; // includes delete

	const LOCK_TOPIC = 16;

	const STICKY_TOPIC = 16;

	const BYPASS_RESTRICTIONS = 32;

	const ADD_FORUMS = 64;


	public static function hasPermission ($action)
	{
		$user = self::$app['session']->get('user');

		if (!$user)
		{
			return false;
		}

		$constant = constant('self::' . $action);

		return $user['group']['permission'] & $constant;
	}

	public static function setPermissions (array $permissions)
	{
		$_perms = null;

		foreach ($permissions as $permission)
		{
			$const = constant('self::' . $permission);
			
			if ($_perms === null)
			{
				$_perms = $const;
			}
			else
			{
				$_perms |= $const;
			}
		}

		return $_perms;
	}
}
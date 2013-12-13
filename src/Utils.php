<?php

use Silex\Application;

class Utils
{
	public static function bbcode ($string)
	{
		$bb = array(
			'|\[b\](.*?)\[\/b\]|',
			'|\[update\](.*)\[\/update\]|s',
			'|\[i\](.*?)\[\/i\]|',
			'|\[u\](.*?)\[\/u\]|',
			'|\[img\](.*?)\[\/img\]|',
			'|\[url=(.*?)\](.*?)\[\/url\]|',
			'|\[color=(.*?)\](.*?)\[\/color\]|',
			'|\[size=(.*?)\](.*?)\[\/size\]|',
			'|\[quote=([a-zA-Z0-9_-]+)\]|si',
			'|\[\/quote\]|si',
			'|\[quote\]|si',
			'|\[\/quote\]|si',
			'|\[code=(.*?)\](.*?)\[\/code\]|sie',
			'|\[unord\]|',
			'|\[\/unord\]|',
			"|\[ord=(.*?)\]|",
			'|\[\/ord\]|',
			"|\[\*\](.*?)\\n|",
			'|\[video\](.*?)\[\/video\]|e',
			'|\[share\](.*?)\[\/share\]|e'
		);
		$html = array(
			'<strong>\\1</strong>',
			'<div class="post-update">\\1</div>',
			'<em>\\1</em>',
			'<span style="text-decoration:underline;">\\1</span>',
			'<img src="\\1" alt="\\1" />',
			'<a href="\\1">\\2</a>',
			'<span style="color:\\1">\\2</span>',
			'<span style="font-size:\\1px">\\2</span>',
			'<blockquote class="quote"><div class="quote_header"><p>\\1 said:</p></div><div class="quote_content"><p>',
			'</p></div></blockquote>',
			'<blockquote class="quote"><div class="quote_header"><p>Someone said:</p></div><div class="quote_content"><p>',
			'</p></div></blockquote>',
			"self::parse_code('\\1', '\\2')",
			'<ul style="margin:0;">',
			'</ul>',
			'<ol start="\\1">',
			'</ol>',
			'<li>\\1</li>',
			"self::create_video('\\1');",
			"self::share_link('\\1');"
		);

		$string = preg_replace($bb, $html, $string);

		return $string;
	}

	public static function share_link ()
	{

	}

	public static function create_video ()
	{
		
	}

	public static function truncate ($string, $length)
	{
		return strlen($string) > $length ? substr($string, 0, $length - 2) . '..' : $string;
	}
}
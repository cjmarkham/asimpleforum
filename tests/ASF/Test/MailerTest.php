<?php

namespace ASF\Test;

require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';

class MailerTest extends \PHPUnit_Framework_TestCase
{
	public function testSend()
	{	
		\Mailer::setTemplate('test');
		$send = \Mailer::send('doddsey65@hotmail.com', 'test@asimpleforum.com', 'Test Email');

		$this->assertTrue($send);
	}

	public function testCompile()
	{
		\Mailer::setTemplate('test', array('name' => 'Carl'));
		\Mailer::compile();

		$this->assertEquals(\Mailer::$template, 'Carl');
	}

	public function testAssert ()
	{
		$this->assertTrue(true);
	}
}
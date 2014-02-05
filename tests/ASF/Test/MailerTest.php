<?php

namespace ASF\Test;

class MailerTest extends \PHPUnit_Framework_TestCase
{
	public function testSend()
	{	
		$set_template = \ASF\Mailer::setTemplate('foo');
		
		$this->assertFalse($set_template);
	}

	public function testCompile()
	{
		\ASF\Mailer::setTemplate('test', array('name' => 'Carl'));
		\ASF\Mailer::compile();

		$this->assertEquals(\ASF\Mailer::$template, 'Carl');
	}
}
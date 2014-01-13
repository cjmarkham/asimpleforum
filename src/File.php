<?php

class File
{
	private $path;
	private $content;

	public function __construct($path)
	{	
		$this->path = $path;
	}

	public function read()
	{
		$this->content = file_get_contents($this->path);
		return $this;
	}

	public function jsonDecode($asObject = false)
	{
		$this->content = json_decode($this->content, $asObject);
		return $this;
	}

	public function getContent()
	{
		return $this->content;
	}
}
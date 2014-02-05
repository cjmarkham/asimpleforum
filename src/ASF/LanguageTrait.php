<?php

namespace ASF;

trait LanguageTrait
{
	public function phrase ($phrase, $replacements = array())
	{
		$language = $this['language'];

		return $language->phrase($phrase, $replacements);
	}
}
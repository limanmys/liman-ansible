<?php

namespace App\Helpers;

use Illuminate\Validation;
use Illuminate\Translation;
use Illuminate\Filesystem\Filesystem;

class Validator
{
	private $factory;

	public function __construct()
	{
		$this->factory = new Validation\Factory($this->loadTranslator());
	}

	protected function loadTranslator()
	{
		global $limanData;
		$filesystem = new Filesystem();
		$loader = new Translation\FileLoader($filesystem, getPath('/lang'));
		$loader->addNamespace('lang', getPath('/lang'));
		$loader->load('en', 'validation', 'lang');
		return new Translation\Translator($loader, $limanData['locale']);
	}

	public function __call($method, $args)
	{
		return call_user_func_array([$this->factory, $method], $args);
	}
}

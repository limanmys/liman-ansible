<?php

namespace App\Helpers;

class Formatter
{
	private $text;
	private $attributes;

	public function __construct(string $text, array $attributes = [])
	{
		$this->text = $text;
		$this->attributes = $attributes;
	}

	public function process()
	{
		foreach ($this->attributes as $attribute => $value) {
			$this->text = str_replace(
				"@{:$attribute}",
				$this->clean($value),
				$this->text
			);
			$this->text = str_replace(
				"{:$attribute}",
				$this->cleanWithoutQuotes($value),
				$this->text
			);
			$this->text = str_replace(":$attribute:", $value, $this->text);
		}
		return $this->text;
	}

	private function cleanWithoutQuotes($value)
	{
		return preg_replace(
			'/^(\'(.*)\'|"(.*)")$/',
			'$2$3',
			$this->clean($value)
		);
	}

	private function clean($value)
	{
		return escapeshellcmd(escapeshellarg($value));
	}

	public static function run(string $text, array $attributes = [])
	{
		return (new self($text, $attributes))->process();
	}
}

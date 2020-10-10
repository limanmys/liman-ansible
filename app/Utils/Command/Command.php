<?php
namespace App\Utils\Command;

use App\Helpers\Formatter;
use ReflectionClass;
use Exception;

class Command
{
	private static $engine = CommandEngine::class;

	public static function run($command, $attributes = [])
	{
		return trim(self::$engine::run(Formatter::run($command, $attributes)));
	}

	public static function runSudo($command, $attributes = [])
	{
		return self::run(self::$engine::sudo() . $command, $attributes);
	}

	public static function bindEngine($engine)
	{
		$class = new ReflectionClass($engine);
		if (!$class->implementsInterface(ICommandEngine::class)) {
			throw new Exception('Engine must implement ICommandEngine');
		}
		self::$engine = $engine;
	}

	public static function getEngine()
	{
		return self::$engine;
	}

	public static function bindDefaultEngine()
	{
		self::$engine = CommandEngine::class;
	}
}

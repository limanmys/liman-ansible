<?php
namespace App\Utils\Command;

class CommandEngine implements ICommandEngine
{
	public static function run($command)
	{
		return runCommand($command);
	}

	public static function sudo()
	{
		return sudo();
	}
}

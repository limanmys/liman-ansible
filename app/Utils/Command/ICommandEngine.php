<?php
namespace App\Utils\Command;

interface ICommandEngine
{
	public static function run($command);

	public static function sudo();
}

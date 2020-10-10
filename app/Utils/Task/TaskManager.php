<?php
namespace App\Utils\Task;

use App\Helpers\Formatter;

class TaskManager
{
	public static function get(string $taskName, array $attributes = [])
	{
		$task = Formatter::run('App\\Tasks\\{:taskName}', [
			'taskName' => $taskName
		]);
		return new $task($attributes);
	}
}

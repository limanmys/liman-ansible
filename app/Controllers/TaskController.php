<?php
namespace App\Controllers;

use App\Utils\Task\TaskManager;

class TaskController
{
	public function runTask()
	{
		$taskName = request('name');
		$attributes = (array) json_decode(request('attributes'));
		return respond(TaskManager::get($taskName, $attributes)->run());
	}

	public function checkTask()
	{
		$taskName = request('name');
		$attributes = (array) json_decode(request('attributes'));
		return respond(TaskManager::get($taskName, $attributes)->check());
	}
}

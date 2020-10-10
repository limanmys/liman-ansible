<?php
namespace App\Utils\Task;

abstract class TaskRunStatusEnum
{
	const Started = 'started';
	const Failed = 'failed';
	const Conflict = 'conflict';
}

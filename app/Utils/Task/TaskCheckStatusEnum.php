<?php
namespace App\Utils\Task;

abstract class TaskCheckStatusEnum
{
	const Pending = 'pending';
	const Failed = 'failed';
	const Success = 'success';
}

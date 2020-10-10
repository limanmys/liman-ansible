<?php
namespace App\Utils\Task;

class TaskCheckStatus
{
	public $status;
	public $output;

	public function __construct(string $status, string $output)
	{
		$this->status = $status;
		$this->output = $output;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getOutput()
	{
		return $this->output;
	}
}

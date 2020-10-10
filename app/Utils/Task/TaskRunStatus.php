<?php
namespace App\Utils\Task;

class TaskRunStatus
{
	public $status;
	public $output;
	public $description;

	public function __construct(
		string $status,
		string $output,
		string $description
	) {
		$this->status = $status;
		$this->output = $output;
		$this->description = $description;
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

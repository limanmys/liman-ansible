<?php
namespace App\Utils\Task;

use App\Helpers\Formatter;
use App\Utils\Command\Command;

abstract class Task
{
	protected $description;
	protected $command;
	protected $checkCommand = null;
	protected $sudoRequired = false;
	protected $logFile;
	protected $control;
	protected $attributes = [];

	public function run()
	{
		$this->before();
		$command = Formatter::run($this->command, $this->attributes);
		if ($this->checkFirst()->getStatus() == TaskCheckStatusEnum::Pending) {
			return new TaskRunStatus(
				TaskRunStatusEnum::Conflict,
				'',
				$this->description
			);
		}
		$status = (bool) $this->command(
			"bash -c '$command > @{:logFile} 2>&1 & disown && echo 1 || echo 0'",
			['logFile' => $this->logFile]
		);
		$processOutput = $this->command(
			'cat @{:logFile} && truncate -s 0 @{:logFile} 2> /dev/null',
			[
				'logFile' => $this->logFile
			]
		);
		$processStatus = $status
			? TaskRunStatusEnum::Started
			: TaskRunStatusEnum::Failed;
		return new TaskRunStatus(
			$processStatus,
			$processOutput,
			$this->description
		);
	}

	public function checkFirst()
	{
		$status = (bool) $this->command(
			'ps aux | grep @{:control} | grep -v grep 2>/dev/null 1>/dev/null && echo 1 || echo 0',
			['control' => $this->control]
		);
		$processStatus = $status
			? TaskCheckStatusEnum::Pending
			: TaskCheckStatusEnum::Success;
		return new TaskCheckStatus($processStatus, '');
	}

	public function check()
	{
		$status = (bool) $this->command(
			'ps aux | grep @{:control} | grep -v grep 2>/dev/null 1>/dev/null && echo 1 || echo 0',
			['control' => $this->control]
		);
		$processStatus = $status
			? TaskCheckStatusEnum::Pending
			: TaskCheckStatusEnum::Success;
		if (
			$this->checkCommand &&
			$processStatus == TaskCheckStatusEnum::Success
		) {
			$status = (bool) $this->command(
				'bash -c @{:checkCommand} 2>/dev/null 1>/dev/null && echo 1 || echo 0',
				['checkCommand' => $this->checkCommand]
			);
			$processStatus = $status
				? TaskCheckStatusEnum::Success
				: TaskCheckStatusEnum::Failed;
		}
		
		$processOutput = $this->command(
			'cat @{:logFile} && truncate -s 0 @{:logFile} 2> /dev/null',
			['logFile' => $this->logFile]
		);
		if ($processStatus == TaskCheckStatusEnum::Success) {
			$this->after();
		}
		return new TaskCheckStatus($processStatus, $processOutput);
	}

	private function command(string $command, array $attributes = [])
	{
		if ($this->sudoRequired) {
			return Command::runSudo($command, $attributes);
		}
		return Command::run($command, $attributes);
	}

	protected function before()
	{
	}

	protected function after()
	{
	}
}

<?php

namespace App\Tasks;

use Liman\Toolkit\Formatter;
use Liman\Toolkit\RemoteTask\Task;

class RunPlaybook extends Task
{
	protected $description = 'Playbook çalıştırılıyor...';
	protected $command = 'ansible-playbook /var/playbooks/{:filename} -i /etc/ansible/hosts';
	protected $sudoRequired = false;
	protected $control = 'ansible-playbook';

	public function __construct(array $attributes = [])
	{
		if (!isset($attributes['filename'])) {
			throw new \Exception('filename is required');
		}

		$this->attributes = $attributes;
		$this->logFile = Formatter::run('/tmp/ansible-playbook-{:filename}', [
			'filename' => $attributes['filename']
		]);
	}

	protected function before()
	{
		//example
		//dd($this->attributes);
	}

	protected function after()
	{
	}
}

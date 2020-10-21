<?php

namespace App\Tasks;

use App\Helpers\Formatter;
use App\Utils\Task\Task;

class InstallPackage extends Task
{
	protected $description = 'Paket indiriliyor...';
	protected $command = 'DEBIAN_FRONTEND=noninteractive apt install ansible -qqy';
	protected $sudoRequired = true;
	protected $control = 'apt install';

	public function __construct(array $attributes = [])
	{
		$this->attributes = $attributes;
		$this->logFile = Formatter::run('/tmp/apt-install-ansible.txt');
	}

	protected function before()
	{
		//example
		//dd($this->attributes);
	}

	protected function after()
	{
		//example
		//dd($this->attributes);
	}
}

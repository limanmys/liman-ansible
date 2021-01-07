<?php

namespace App\Tasks;

use Liman\Toolkit\Formatter;
use Liman\Toolkit\RemoteTask\Task;
use Liman\Toolkit\Shell\Command;

class InstallPackage extends Task
{
	protected $description = 'Paket indiriliyor...';
	protected $command = 'DEBIAN_FRONTEND=noninteractive apt install ansible sshpass unzip -qqy';
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
		Command::runSudo("sed -i '/\[defaults\]/a host_key_checking = False' /etc/ansible/ansible.cfg"); //Fingerprint check off
	}
}

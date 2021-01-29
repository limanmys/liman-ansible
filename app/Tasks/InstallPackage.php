<?php

namespace App\Tasks;

use Liman\Toolkit\Formatter;
use Liman\Toolkit\OS\Distro;
use Liman\Toolkit\RemoteTask\Task;
use Liman\Toolkit\Shell\Command;

class InstallPackage extends Task
{
	protected $description = 'Paket indiriliyor...';
	protected $sudoRequired = true;

	public function __construct(array $attributes = [])
	{
		$this->control = Distro::debian(
			'apt install'
		)->centos(
			'yum install'
		)->get();

		$this->command = Distro::debian(
			'DEBIAN_FRONTEND=noninteractive apt install ansible sshpass unzip -qqy'
		)->centos(
			'yum install -y epel-release sshpass unzip; yum install -y ansible'
		)->get();

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

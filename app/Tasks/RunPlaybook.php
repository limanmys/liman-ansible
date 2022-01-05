<?php

namespace App\Tasks;

use Liman\Toolkit\Formatter;
use Liman\Toolkit\RemoteTask\Task;
use Liman\Toolkit\Shell\Command;
/*$playbookname_field = request('playbookname');
		$sudopass_field = request('sudopass');
		
		Command::run("rm /var/playbooks2/test.txt");
		Command::run("touch /var/playbooks2/test.txt");
		Command::run(" /var/playbooks/@{:playbookname_field} --extra-vars 'ansible_sudo_pass=@{:sudopass_field}'", [
			'playbookname_field' => $playbookname_field,
			'sudopass_field' => $sudopass_field
]);*/
class RunPlaybook extends Task
{
	protected $description = 'Playbook çalıştırılıyor...';
	protected $command = "ansible-playbook /var/playbooks/{:filename} -i /etc/ansible/hosts --extra-vars 'ansible_sudo_pass={:passText}'";
	protected $sudoRequired = false;
	protected $control = 'ansible-playbook';

	public function __construct(array $attributes = [])
	{
		if (!isset($attributes['filename'])) {
			throw new \Exception('filename is required');
		}

		$this->attributes = $attributes;
		$this->logFile = Formatter::run('/tmp/ansible-playbook-{:filename}', [
			'filename' => $attributes['filename'],
			'passText' => $attributes['passText']
		]);
	}

	protected function before()
	{
		$checkFinger = (bool) Command::runSudo(
			"cat /etc/ansible/ansible.cfg | grep '^\s*host_key_checking\s*=\s*False' 2>/dev/null  1>/dev/null && echo 1 || echo 0"
		);
		//Fingerprint check off
		if (!$checkFinger) {
			Command::runSudo(
				"sed -i '/\[defaults\]/a host_key_checking = False' /etc/ansible/ansible.cfg"
			);
		}
	}

	protected function after()
	{
		//example
		//dd($this->attributes);
	}
}

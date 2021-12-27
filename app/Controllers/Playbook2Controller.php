<?php

namespace App\Controllers;

use Liman\Toolkit\Shell\Command;
class Playbook2Controller
{
	public function get(){
		$checkDirectory = Command::runSudo(
			'[ -d /var/playbooks2 ] && echo 1 || echo 0'
		);
		if ($checkDirectory == '0') {
			Command::runSudo('mkdir /var/playbooks2');
		}
		$fileList = Command::runSudo(
			"ls -lR /var/playbooks2 | grep '.yml$' | awk '{{print $9}}'"
		);
		return $fileList;
	}

	public function listHosts()
    {
		$playbookname_field = request('playbookname');
		$sudopass_field = request('sudopass');
		
		Command::run("rm /var/playbooks2/test.txt");
		Command::run("touch /var/playbooks2/test.txt");
		Command::run("ansible-playbook /var/playbooks2/@{:playbookname_field} --extra-vars 'ansible_sudo_pass=@{:sudopass_field}'", [
			'playbookname_field' => $playbookname_field,
			'sudopass_field' => $sudopass_field
		]);

		$output = Command::run('cat /var/playbooks2/test.txt');
		
		$fileJson = [];
        if ($output != '') {
           	$fileArray = explode("\n", $output);
           	$fileJson = collect($fileArray)->map(function ($i) {
               	return ['name' => $i];
           	}, $fileArray);
        }
		
        return view('table', [
           	"value" => $fileJson,
           	"title" => ["Value"],
           	"display" => ["name"],
        ]);	
    }
	
	public function saveLog()
	{
		$output = Command::run('cat /var/playbooks2/test.txt');
		$logFileContent = $output;
		$logFileName = request('logFileName');

		$checkFile = Command::runSudo(
			'[ -f /var/playbook-logs/{:logFileName} ] && echo 1 || echo 0',
			[
				'logFileName' => $logFileName
			]
		);
		if ($checkFile == '1') {
			return respond('Bu isimde log bulunmaktadır', 201);
		}
		$checkDirectory = Command::runSudo(
			'[ -d /var/playbook-logs ] && echo 1 || echo 0'
		);
		if ($checkDirectory == '0') {
			Command::runSudo('mkdir /var/playbook-logs');
		}
		if($output != ""){
			Command::runSudo(
				"bash -c \"echo @{:logFileContent} | base64 -d | tee /var/playbook-logs/{:logFileName}\"",
				[
					'logFileContent' => base64_encode($logFileContent),
					'logFileName' => $logFileName
				]
			);
			return respond('Kaydedildi', 200);
		}
		else
			return respond('Kayıt başarısız!.. Hatalı sudo şifresi)', 201);
	}
}

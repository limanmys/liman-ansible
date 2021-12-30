<?php

namespace App\Controllers;

use Liman\Toolkit\Shell\Command;
class Playbook2Controller
{
	public function get2(){
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

	public function runPlaybook2()
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
		if($output!="")
			return $output;
		else
			return respond('Hatalı sudo şifresi veya hatalı playbook)', 201);
    }
	
	public function saveLog2()
	{
		$output = Command::run('cat /var/playbooks2/test.txt');
		$logFileContent = $output;
		$logFileName = request('logFileName');
		//dd(user()->name);
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
			return respond('Kayıt başarısız!.. Boş veri)', 201);
	}

	public function getLog2()
	{
		$checkDirectory = Command::run(
			'[ -d /var/playbook-logs ] && echo 1 || echo 0'
		);
		$data = [];
		if ($checkDirectory == '1') {
			$filenames = Command::run(
				"ls -lh /var/playbook-logs| grep '^-' | awk '{print $5,$6,$7,$8,$9,$4}'"
			);
			$filenamesArray = explode("\n", trim($filenames));
			foreach ($filenamesArray as $value) {
				if (empty(trim($value))) {
					continue;
				}
				$itemArray = explode(' ', trim($value));
				$item = [
					'name' => $itemArray[4],
					'size' => $itemArray[0],
					'user' => $itemArray[5],
					'date' => join('-', [
						$itemArray[1],
						$itemArray[2],
						$itemArray[3]
					])
				];
				array_push($data, $item);
			}
			//dd(user());
		}
		return view('table', [
			'value' => $data,
			'title' => ['Dosya Adı', 'Boyut', 'Kullanıcı', 'Tarih'],
			'display' => ['name', 'size', 'user', 'date'],
			'menu' => [
				'Gör' => [
					'target' => 'showLogContent2',
					'icon' => 'fa-eye'
				],
				'Sil' => [
					'target' => 'deleteLog2',
					'icon' => 'fa-trash'
				]
			]
		]);
	}

	public function getContent2()
	{
		$fileName = request('fileName');
		$output = Command::runSudo('cat  /var/playbook-logs/{:fileName}', [
			'fileName' => $fileName
		]);
		return respond($output, 200);
	}

	public function delete2()
	{
		$fileName = request('fileName');
		$result = Command::runSudo('rm -rf /var/playbook-logs/{:fileName}', [
			'fileName' => $fileName
		]);

		if (trim($result) == '') {
			return respond('Silindi', 200);
		} else {
			return respond($result, 201);
		}
	}

}

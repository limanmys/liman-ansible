<?php

namespace App\Controllers;

use Liman\Toolkit\Shell\Command;

class PlaybookController
{
	public function get()
	{
		$checkDirectory = Command::runSudo(
			'[ -d /var/playbooks ] && echo 1 || echo 0'
		);
		if ($checkDirectory == '0') {
			Command::runSudo('mkdir /var/playbooks');
		}
		$fileJson = [];
		$fileList = Command::runSudo(
			"ls -l /var/playbooks | awk '{{print $9}}'"
		);
		if ($fileList != '') {
			$fileArray = explode("\n", $fileList);
			$fileJson = collect($fileArray)->map(function ($i) {
				return ['name' => $i];
			}, $fileArray);
		}
		return view('table', [
			'value' => $fileJson,
			'title' => ['Dosya Adı'],
			'display' => ['name'],
			'onclick' => 'openRunPlaybookComponent',
			'menu' => [
				'Gör' => [
					'target' => 'showPlaybookContent',
					'icon' => 'fa-eye'
				],
				'Düzenle' => [
					'target' => 'openPlaybookEditComponent',
					'icon' => 'fa-edit'
				],
				'Çalıştır' => [
					'target' => 'openRunPlaybookComponent',
					'icon' => 'fa-play'
				],
				'Sil' => [
					'target' => 'deletePlaybook',
					'icon' => 'fa-trash'
				]
			]
		]);
	}

	public function getOutput()
    {
		$output = Command::run('cat /var/playbooks/test.txt');
		if($output!="")
			return $output;
		else
			return respond('Playbook çıktısı bulunamadı..!', 201);
    }

	public function getLog()
	{
		$checkDirectory = Command::run(
			'[ -d /var/playbook-logs ] && echo 1 || echo 0'
		);
		$data = [];
		if ($checkDirectory == '1') {
			$filenames = Command::run(
				"ls -lh /var/playbook-logs| grep '^-' | awk '{print $5,$6,$7,$8,$9}'"
			);
			$filenamesArray = explode("\n", trim($filenames));
			foreach ($filenamesArray as $value) {
				if (empty(trim($value))) {
					continue;
				}
				$itemArray = explode(' ', trim($value));
				$nameArray = explode("-.-", trim($itemArray[4]));
				$item = [
					'name' => $nameArray[0],
					'size' => $itemArray[0],
					'user' => $nameArray[1],
					'date' => join('-', [
						$itemArray[1],
						$itemArray[2],
						$itemArray[3]
					])
				];
				array_push($data, $item);
			}
		}
		return view('table', [
			'value' => $data,
			'title' => ['Dosya Adı', 'Boyut', 'Kullanıcı', 'Tarih'],
			'display' => ['name', 'size', 'user', 'date'],
			"onclick" => "showLogContent",
			'menu' => [
				'Gör' => [
					'target' => 'showLogContent',
					'icon' => 'fa-eye'
				],
				'Sil' => [
					'target' => 'deletePlaybookLog',
					'icon' => 'fa-trash'
				]
			]
		]);
	}

	public function getContent()
	{
		$fileName = request('fileName');
		$output = Command::runSudo('cat /var/playbooks/{:fileName} | base64', [
			'fileName' => $fileName
		]);

		return respond(base64_decode($output), 200);
	}

	public function create()
	{
		$fileName = request('fileName');
		$fileContent = request('fileContent');

		$checkFile = Command::runSudo(
			'[ -f /var/playbooks/{:fileName} ] && echo 1 || echo 0',
			[
				'fileName' => $fileName
			]
		);

		if ($checkFile == '1') {
			return respond('Dosya zaten bulunmaktadır', 201);
		}

		$result = Command::runSudo(
			"sh -c \"echo @{:fileContent}| base64 -d | tee /var/playbooks/{:fileName}\"  1>/dev/null",
			[
				'fileContent' => base64_encode($fileContent),
				'fileName' => $fileName
			]
		);

		if (trim($result) == '') {
			return respond('Oluşturuldu', 200);
		} else {
			return respond($result, 201);
		}
	}

	public function edit()
	{
		$fileName = request('fileName');
		$contentFile = request('contentFile');
		$result = Command::runSudo(
			"sh -c \"echo @{:contentFile}| base64 -d | tee /var/playbooks/{:fileName}\"  1>/dev/null",
			[
				'contentFile' => base64_encode($contentFile),
				'fileName' => $fileName
			]
		);

		if (trim($result) == '') {
			return respond('Güncellendi', 200);
		} else {
			return respond($result, 201);
		}
	}

	public function delete()
	{
		$fileName = request('fileName');

		$result = Command::runSudo('rm -rf /var/playbooks/{:fileName}', [
			'fileName' => $fileName
		]);

		if (trim($result) == '') {
			return respond('Silindi', 200);
		} else {
			return respond($result, 201);
		}
	}

	public function deletePlaybookLog()
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

	public function run()
	{
		Command::run("rm /var/playbooks/test.txt");
		Command::run("touch /var/playbooks/test.txt");
		Command::runSudo(
			"sed -i 's/hosts: .*/hosts: {:group}/g' /var/playbooks/{:filename}",
			[
				'filename' => request('filename'),
				'group' => request('group')
			]
		);

		return respond(
			view('task', [
				'onFail' => 'onTaskFail',
				'tasks' => [
					0 => [
						'name' => 'RunPlaybook',
						'attributes' => [
							'filename' => request('filename'),
							'group' => request('group'),
							'passText' => request('passText')
						]
					]
				]
			]),
			200
		);
	}

	public static function getHostsSelect()
	{
		$hostsfilepath = '/etc/ansible/hosts';
		$output = Command::runSudo("cat {:hostsfilepath} | grep -v '^#'", [
			'hostsfilepath' => $hostsfilepath
		]);
		preg_match_all('/\[(.*)\]/', $output, $matches);
		return collect($matches[1])
			->map(function ($i) {
				return ['name' => $i];
			}, $matches[1])
			->pluck('name', 'name')
			->toArray();
	}

	public function savePlaybookTask()
	{
		$logFileContent = request('logFileContent');
		$logFileName = request('logFileName') . "-.-" . user()->name;

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
		Command::runSudo(
			"bash -c \"echo @{:logFileContent} | base64 -d | tee /var/playbook-logs/{:logFileName}\"",
			[
				'logFileContent' => base64_encode($logFileContent),
				'logFileName' => $logFileName
			]
		);
		return respond('Kaydedildi', 200);
	}
	public function savePlaybookOutput()
	{
		$textArea = request('textArea');
		$output = Command::run('cat /var/playbooks/test.txt');
		$logFileContent = $output;
		$logFileName = request('logFileName') . "-.-" . user()->name;
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
		if($textArea != ""){
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
			return respond('Kayıt başarısız!.. (Boş veri)', 201);
	}
	
}

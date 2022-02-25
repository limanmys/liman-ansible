<?php

namespace App\Controllers;

use Liman\Toolkit\Shell\Command;

class PlaybookController
{
	public function get()
	{
		$playbookPath = extensionDB('playbookPath');
		$checkDirectory = Command::runSudo(
			'[ -d {:playbookPath} ] && echo 1 || echo 0',
			[
				'playbookPath' => $playbookPath
			]
		);
		if ($checkDirectory == '0') {
			Command::runSudo('mkdir {:playbookPath}', [
				'playbookPath' => $playbookPath
			]);
		}
		$fileJson = [];
		$fileList = Command::runSudo(
			"ls -l {:playbookPath} | awk '{{print $9}}'",
			[
				'playbookPath' => $playbookPath
			]
		);
		if (empty($output)) {
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
		$output = Command::run('cat {:playbookPath}/test.txt', [
			'playbookPath' => extensionDB('playbookPath')
		]);
		if (!empty($output)) {
			return $output;
		} else {
			return respond('Playbook çıktısı bulunamadı..!', 201);
		}
	}

	public function getLog()
	{
		$checkDirectory = Command::run(
			'[ -d /var/playbook-logs ] && echo 1 || echo 0'
		);
		$data = [];
		if ((bool) $checkDirectory) {
			$filenames = Command::run(
				"ls -lh /var/playbook-logs| grep '^-' | awk '{print $5,$6,$7,$8,$9}'"
			);
			$filenamesArray = explode("\n", trim($filenames));
			foreach ($filenamesArray as $value) {
				if (empty(trim($value))) {
					continue;
				}
				$itemArray = explode(' ', trim($value));
				$nameArray = explode('-.-', trim($itemArray[4]));
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
			'onclick' => 'showLogContent',
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

	public function getContentLog()
	{
		$output = Command::runSudo('cat /var/playbook-logs/{:fileName}', [
			'fileName' => request('fileName')
		]);
		return respond($output, 200);
	}

	public function getContent()
	{
		$fileName = preg_replace("/\r|\n/", '', request('fileName'));
		$output = Command::runSudo('cat {:playbookPath}/{:fileName} | base64', [
			'playbookPath' => extensionDB('playbookPath'),
			'fileName' => $fileName
		]);
		return respond(base64_decode($output), 200);
	}

	public function create()
	{
		$checkFile = Command::runSudo(
			'[ -f {:playbookPath}/{:fileName} ] && echo 1 || echo 0',
			[
				'playbookPath' => extensionDB('playbookPath'),
				'fileName' => request('fileName')
			]
		);

		if (((bool) $checkFile)) {
			return respond('Dosya zaten bulunmaktadır', 201);
		}

		$result = Command::runSudo(
			"sh -c \"echo @{:fileContent}| base64 -d | tee {:playbookPath}/{:fileName}\"  1>/dev/null",
			[
				'playbookPath' => extensionDB('playbookPath'),
				'fileContent' => base64_encode(request('fileContent')),
				'fileName' => request('fileName')
			]
		);

		if (empty(trim($result))) {
			return respond('Oluşturuldu', 200);
		} else {
			return respond($result, 201);
		}
	}

	public function edit()
	{
		$result = Command::runSudo(
			"sh -c \"echo @{:contentFile}| base64 -d | tee {:playbookPath}/{:fileName}\"  1>/dev/null",
			[
				'playbookPath' => extensionDB('playbookPath'),
				'contentFile' => base64_encode(request('contentFile')),
				'fileName' => preg_replace("/\r|\n/", '', request('fileName'))
			]
		);

		if (empty(trim($result))) {
			return respond('Güncellendi', 200);
		} else {
			return respond($result, 201);
		}
	}

	public function delete()
	{
		$result = Command::runSudo('rm -rf {:playbookPath}/{:fileName}', [
			'playbookPath' => extensionDB('playbookPath'),
			'fileName' => preg_replace("/\r|\n/", '', request('fileName'))
		]);

		if (empty(trim($result))) {
			return respond('Silindi', 200);
		} else {
			return respond($result, 201);
		}
	}

	public function deletePlaybookLog()
	{
		$result = Command::runSudo('rm -rf /var/playbook-logs/{:fileName}', [
			'fileName' => request('fileName')
		]);

		if (empty(trim($result))) {
			return respond('Silindi', 200);
		} else {
			return respond($result, 201);
		}
	}

	public function run()
	{
		$playbookPath = extensionDB('playbookPath');
		$fileName = preg_replace("/\r|\n/", '', request('filename'));

		Command::run('rm {:playbookPath}/test.txt', [
			'playbookPath' => $playbookPath
		]);
		Command::run('touch {:playbookPath}/test.txt', [
			'playbookPath' => $playbookPath
		]);
		Command::runSudo(
			"sed -i 's/hosts: .*/hosts: {:group}/g' {:playbookPath}/{:filename}",
			[
				'playbookPath' => $playbookPath,
				'filename' => $fileName,
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
							'playbookPath' => $playbookPath,
							'filename' => $fileName,
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
		$hostsfilepath = '/etc/ansible/hosts2';
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
		$logFileName = request('logFileName') . '-.-' . user()->name;
		$checkFile = Command::runSudo(
			'[ -f /var/playbook-logs/{:logFileName} ] && echo 1 || echo 0',
			[
				'logFileName' => $logFileName
			]
		);
		if ((bool) $checkFile) {
			return respond('Bu isimde log bulunmaktadır', 201);
		}
		$checkDirectory = Command::runSudo(
			'[ -d /var/playbook-logs ] && echo 1 || echo 0'
		);
		if ((bool) !$checkDirectory) {
			Command::runSudo('mkdir /var/playbook-logs');
		}
		Command::runSudo(
			"bash -c \"echo @{:logFileContent} | base64 -d | tee /var/playbook-logs/{:logFileName}\"",
			[
				'logFileContent' => base64_encode(request('logFileContent')),
				'logFileName' => $logFileName
			]
		);
		return respond('Kaydedildi', 200);
	}
	public function savePlaybookOutput()
	{
		$textArea = request('textArea');
		$logFileContent = Command::run('cat {:playbookPath}/test.txt', [
			'playbookPath' => extensionDB('playbookPath')
		]);
		$logFileName = request('logFileName') . '-.-' . user()->name;
		$checkFile = Command::runSudo(
			'[ -f /var/playbook-logs/{:logFileName} ] && echo 1 || echo 0',
			[
				'logFileName' => $logFileName
			]
		);
		if ((bool) $checkFile) {
			return respond('Bu isimde log bulunmaktadır', 201);
		}
		$checkDirectory = Command::runSudo(
			'[ -d /var/playbook-logs ] && echo 1 || echo 0'
		);
		if (((bool) !$checkDirectory)) {
			Command::runSudo('mkdir /var/playbook-logs');
		}
		if (!empty($textArea)) {
			Command::runSudo(
				"bash -c \"echo @{:logFileContent} | base64 -d | tee /var/playbook-logs/{:logFileName}\"",
				[
					'logFileContent' => base64_encode(
						str_replace("\r\n", "\n", $textArea)
					),
					'logFileName' => $logFileName
				]
			);
			return respond('Kaydedildi', 200);
		} else {
			return respond('Kayıt başarısız!.. (Boş veri)', 201);
		}
	}
}

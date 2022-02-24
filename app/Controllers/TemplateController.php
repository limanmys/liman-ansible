<?php

namespace App\Controllers;

use Liman\Toolkit\Shell\Command;

class TemplateController
{
	public function get()
	{
		$checkDirectory = Command::runSudo(
			'[ -d /var/templates ] && echo 1 || echo 0'
		);
		if ((bool) !$checkDirectory) {
			Command::runSudo('mkdir /var/templates');
		}
		$fileJson = [];
		$fileList = Command::runSudo(
			"ls -l /var/templates | awk '{{print $9}}'"
		);
		if (!empty($fileList)) {
			$fileArray = explode("\n", $fileList);
			$fileJson = collect($fileArray)->map(function ($i) {
				return [
					'name' => $i,
					'path' => '/var/templates/' . $i
				];
			}, $fileArray);
		}
		return view('table', [
			'value' => $fileJson,
			'title' => ['Dosya Adı', 'Dosya Yolu'],
			'display' => ['name', 'path'],
			'menu' => [
				'Gör' => [
					'target' => 'showTemplateContent',
					'icon' => 'fa-eye'
				],
				'Düzenle' => [
					'target' => 'openTemplateEditComponent',
					'icon' => 'fa-edit'
				],
				'Sil' => [
					'target' => 'deleteTemplate',
					'icon' => 'fa-trash'
				]
			]
		]);
	}

	public function getContent()
	{
		$output = Command::runSudo('cat  /var/templates/{:fileName} | base64', [
			'fileName' => preg_replace("/\r|\n/", "", request('fileName')) 
		]);

		return respond(base64_decode($output), 200);
	}

	public function create()
	{
		$fileName = preg_replace("/\r|\n/", "", request('fileName'));
		$fileContent = str_replace("\r\n","\n",request('fileContent'));

		if (!preg_match('/^[a-z0-9-.]+$/', $fileName)) {
			return respond(
				'Dosya ismi geçersizdir (Türkçe karakter, özel karakter veya boşluk içermemelidir).',
				201
			);
		}

		$checkFile = Command::runSudo(
			'[ -f /var/templates/{:fileName} ] && echo 1 || echo 0',
			[
				'fileName' => $fileName
			]
		);

		if ((bool) $checkFile) {
			return respond('Dosya zaten bulunmaktadır', 201);
		}

		$result = Command::runSudo(
			"sh -c \"echo @{:fileContent}| base64 -d | tee /var/templates/{:fileName}\"  1>/dev/null",
			[
				'fileContent' => base64_encode($fileContent),
				'fileName' => $fileName
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
			"sh -c \"echo @{:contentFile}| base64 -d | tee /var/templates/{:fileName}\"  1>/dev/null",
			[
				'contentFile' => base64_encode(str_replace("\r\n","\n",request('contentFile'))),
				'fileName' => preg_replace("/\r|\n/", "", request('fileName'))
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
		$result = Command::runSudo('rm -rf /var/templates/{:fileName}', [
			'fileName' => request('fileName')
		]);

		if (empty(trim($result))) {
			return respond('Silindi', 200);
		} else {
			return respond($result, 201);
		}
	}
}

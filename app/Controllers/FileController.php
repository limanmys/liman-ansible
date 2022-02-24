<?php

namespace App\Controllers;

use Liman\Toolkit\Shell\Command;

class FileController
{
	function get()
	{
		$result = runScript('jstree.py', '', true);
		return view('components.files-load', [
			'data' => $result
		]);
	}

	function getContent()
	{
		$output = Command::runSudo('cat {:filePath}', [
			'filePath' => request('filePath')
		]);
		return $output;
	}

	function upload()
	{
		$dirName = request('dirName');
		$name = request('name');
		$path = request('path');
		$remotePath = '/tmp/' . str_replace(' ', '', $name);
		$path_parts = pathinfo($path);

		$fileCheck = Command::runSudo(
			' [ -d /opt/varlik/{:dirName} ] 2>/dev/null 1>/dev/null && echo 1 || echo 0',
			[
				'dirName' => $dirName
			]
		);

		if ($fileCheck == '1') {
			return respond('Klasör bulunmaktadır.', 201);
		}

		Command::runSudo('mkdir -p /opt/varlik/{:dirName}', [
			'dirName' => $dirName
		]);

		$output = putFile(getPath($path), $remotePath);

		if ($output !== 'ok') {
			return respond('Dosya Yükleme Başarısız', 201);
		}

		if ($path_parts['extension'] == 'gz') {
			Command::runSudo(
				'tar -xzf {:remotePath} -C /opt/varlik/{:dirName}',
				[
					'dirName' => $dirName,
					'remotePath' => $remotePath
				]
			);
		} elseif ($path_parts['extension'] == 'tar') {
			Command::runSudo(
				'tar -xf {:remotePath} -C /opt/varlik/{:dirName}',
				[
					'dirName' => $dirName,
					'remotePath' => $remotePath
				]
			);
		} elseif ($path_parts['extension'] == 'zip') {
			Command::runSudo(
				'unzip {:remotePath} -d /opt/varlik/{:dirName}',
				[
					'dirName' => $dirName,
					'remotePath' => $remotePath
				]
			);
		} else {
			return respond('Desteklenmeyen dosya tipi.', 201);
		}

		$pathFile = str_replace(' ', '\ ', getPath(quotemeta($path)));
		shell_exec("rm -rf $pathFile");

		Command::runSudo('rm -rf  {:remotePath}', [
			'remotePath' => $remotePath
		]);

		return respond('Başarılı', 200);
	}

	function edit()
	{
		$text = str_replace("\r\n", "\n", request('text'));
		Command::runSudo(
			"bash -c \"echo -e @{:text} | base64 -d | tee @{:filePath}\"",
			[
				'text' => base64_encode($text),
				'filePath' => request('filePath')
			]
		);
		return respond('Başarılı', 200);
	}
}

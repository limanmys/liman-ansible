<?php

namespace App\Controllers;

use Liman\Toolkit\Shell\Command;

class LogController
{
	public function get()
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
			'menu' => [
				'Gör' => [
					'target' => 'showLogContent',
					'icon' => 'fa-eye'
				],
				'Sil' => [
					'target' => 'deleteLog',
					'icon' => 'fa-trash'
				]
			]
		]);
	}

	public function getContent()
	{
		$fileName = request('fileName');
		$output = Command::runSudo('cat  /var/playbook-logs/{:fileName}', [
			'fileName' => $fileName
		]);
		return respond($output, 200);
	}

	public function delete()
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

<?php

namespace App\Controllers;
use App\Utils\Command\Command;

class UserController
{
	protected $userFilePath = '/etc/ansible/users';

	function get()
	{
		$userArray = [];
		$fileCheck = Command::runSudo(
			' [ -f {:userFilePath} ] 2>/dev/null 1>/dev/null && echo 1 || echo 0',
			[
				'userFilePath' => $this->userFilePath
			]
		);
		if ($fileCheck == '0') {
			return respond('notfoundfile', 202);
		}

		$userFileText = str_replace(
			"\n",
			'',
			Command::runSudo('cat {:userFilePath}', [
				'userFilePath' => $this->userFilePath
			])
		);
		$userArray = json_decode($userFileText, true);

		if (!is_array($userArray)) {
			$userArray = [];
		}

		return view('table', [
			'value' => $userArray,
			'title' => ['İsim', 'Password', 'Sudo Yetkisi'],
			'display' => ['name', 'password', 'sudo'],
			'menu' => [
				'Sil' => [
					'target' => 'deleteUser',
					'icon' => 'fa-trash'
				]
			]
		]);
	}

	function add()
	{
		$username = request('username');
		$password = request('password');
		$permission = request('type');

		$fileCheck = Command::runSudo(
			' [ -f {:userFilePath} ] 2>/dev/null 1>/dev/null && echo 1 || echo 0',
			[
				'userFilePath' => $this->userFilePath
			]
		);

		if ($fileCheck == '0') {
			Command::runSudo('touch {:userFilePath}', [
				'userFilePath' => $this->userFilePath
			]);
		}

		$userFileText = str_replace(
			"\n",
			'',
			Command::runSudo('cat {:userFilePath}', [
				'userFilePath' => $this->userFilePath
			])
		);
		$userArray = json_decode($userFileText, true);

		if (is_array($userArray)) {
			foreach ($userArray as $key => $value) {
				if ($value['name'] == trim($username)) {
					return respond('Aynı kullanıcı bulunmaktadır', 201);
				}
			}
		} else {
			$userArray = [];
		}

		$item = [
			'name' => $username,
			'password' => $password,
			'sudo' => $permission
		];
		array_push($userArray, $item);
		$textUserFile = json_encode($userArray);
		$textUserFile = str_replace("\"", "\\\"", $textUserFile);
		$output = Command::runSudo(
			"sh -c \"echo {:textUserFile} > {:userFilePath}\"",
			[
				'textUserFile' => $textUserFile,
				'userFilePath' => $this->userFilePath
			]
		);

		if (trim($output) == '') {
			return respond('Başarıyla Eklendi', 200);
		} else {
			return respond($output, 201);
		}
	}

	function delete()
	{
		$name = request('name');
		$userFileText = str_replace(
			"\n",
			'',
			Command::runSudo('cat {:userFilePath}', [
				'userFilePath' => $this->userFilePath
			])
		);
		$userArray = json_decode($userFileText, true);

		foreach ($userArray as $key => $value) {
			if ($value['name'] == $name) {
				unset($userArray[$key]);
			}
		}
		$userFileText = json_encode($userArray);
		$userFileText = str_replace("\"", "\\\"", $userFileText);
		$output = Command::runSudo(
			"sh -c 'echo $userFileText > {:userFilePath}'",
			['userFilePath' => $this->userFilePath]
		);
		if (trim($output) == '') {
			return respond('Silindi', 200);
		} else {
			return respond($output, 201);
		}
	}
}

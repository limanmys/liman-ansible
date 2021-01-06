<?php

namespace App\Controllers;

use Liman\Toolkit\Shell\SSHEngine;
use Liman\Toolkit\Shell\Command;

class HostsController
{
	protected $hostsFilePath = '/etc/ansible/hosts';

	function deleteGroup()
	{
		$groupName = request('groupName');
		$textHostFile = Command::runSudo(
			'ansible-inventory -i {:hostsFilePath} --list --yaml',
			[
				'hostsFilePath' => $this->hostsFilePath
			]
		);
		$arrayHosts = yaml_parse($textHostFile)['all']['children'][$groupName];
		if (!empty($arrayHosts['hosts'])) {
			return respond('WARNING', 201);
		}
		Command::runSudo(
			"sh -c \"sed -i '/\[\s*{:groupName}\s*\]/d' {:hostsFilePath}\"",
			[
				'groupName' => $groupName,
				'hostsFilePath' => $this->hostsFilePath
			]
		);
		$textHostFile = Command::runSudo(
			'ansible-inventory -i {:hostsFilePath} --list --yaml',
			[
				'hostsFilePath' => $this->hostsFilePath
			]
		);
		$checkGroup = yaml_parse($textHostFile)['all']['children'][$groupName];
		if (empty($checkGroup)) {
			return respond('Silindi', 200);
		} else {
			return respond('Silinemedi', 201);
		}
	}

	function get()
	{
		$output = Command::runSudo("cat {:hostsFilePath} | grep -v '^#'", [
			'hostsFilePath' => $this->hostsFilePath
		]);
		preg_match_all('/\[(.*)\]/', $output, $matches);
		$hostNameArray = collect($matches[1])
			->map(function ($i) {
				return ['name' => $i];
			}, $matches[1])
			->toArray();
		return view('table', [
			'value' => $hostNameArray,
			'title' => ['Host Adı'],
			'display' => ['name'],
			'menu' => [
				'Sil' => [
					'target' => 'deleteGroup',
					'icon' => 'fa-trash'
				]
			],
			'onclick' => 'getHostsContent'
		]);
	}

	function getContent()
	{
		$hostName = request('hostName');
		$output =
			Command::runSudo("cat {:hostsFilePath} | grep -v '^#'", [
				'hostsFilePath' => $this->hostsFilePath
			]) . ' [';

		$output = str_replace("\n", '---', $output);
		preg_match(
			'/\[' . $hostName . '\s*](.*?)(?=\[)/',
			trim($output),
			$matches
		);
		$lines = explode('---', trim($matches[1]));
		$lines = array_filter($lines);
		$data = [];
		foreach ($lines as $key => $line) {
			$lineParts = explode(' ', trim($line));
			sizeof($lineParts) == 1
				? ($sshUser = '-')
				: ($sshUser = trim(explode('=', $lineParts[1])[1]));
			$data[] = [
				'ip' => $lineParts[0],
				'ssh_user' => $sshUser
			];
		}

		return view('table', [
			'value' => $data,
			'title' => ['Ip Adresi', 'Ssh Kullanıcı Adı'],
			'display' => ['ip', 'ssh_user'],
			'menu' => [
				'Ssh Key Ekle' => [
					'target' => 'openAddSshKeyComponent',
					'icon' => 'fa-plus'
				],
				'Ssh Key Kaldır' => [
					'target' => 'openRemoveSshKeyComponent',
					'icon' => 'fa-times-circle'
				],
				'Sil' => [
					'target' => 'deleteClientIpJS',
					'icon' => 'fa-trash'
				]
			]
		]);
	}

	function add()
	{
		validate([
			'ipaddress' => 'required|string',
			'sshUserName' => 'required|string'
		]);

		$hostsname = request('hostsname');
		$ipaddress = request('ipaddress');
		$ansibleSshUser = trim(request('sshUserName'));
		$ansibleSshPass = trim(request('sshUserPass'));
		if ($ansibleSshUser == '') {
			$clientLine = $ipaddress;
		} else {
			$clientLine = "$ipaddress ansible_ssh_user=$ansibleSshUser ansible_ssh_pass=$ansibleSshPass";
		}

		if ($hostsname == 'Grupsuz') {
			$grupsuz = Command::runSudo(
				"cat {:hostsFilePath} | grep -v '^#' | sed -n -e '/\[.*/,/\\$/!p'",
				[
					'hostsFilePath' => $this->hostsFilePath
				]
			);
			$grupsuz = explode("\n", $grupsuz);
			if (in_array($clientLine, $grupsuz)) {
				return respond('Böyle bir client bulunmaktadır.', 201);
			}
			$linenumber = Command::runSudo(
				"cat {:hostsfilepath} | grep -n -v '^#' | sed -n -e '/\[.*/,/\\$/!p' | tail -n 1 | cut -d ':' -f1",
				[
					'hostsfilepath' => $this->hostsfilepath
				]
			);

			$output = Command::runSudo(
				"sh -c \"sed -i '{:linenumber} i {:ipaddress}' {:hostsFilePath}\"",
				[
					'linenumber' => $linenumber,
					'ipaddress' => $ipaddress,
					'hostsFilePath' => $this->hostsFilePath
				]
			);
		} else {
			$output =
				Command::runSudo("cat {:hostsFilePath} | grep -v '^#'", [
					'hostsFilePath' => $this->hostsFilePath
				]) . ' [';
			$output = str_replace("\n", ' ', $output);

			preg_match("/\[$hostsname\](.*?)(?=\[)/", $output, $hostzone);
			if (strpos($hostzone[1], $clientLine) !== false) {
				return respond('Böyle bir client bulunmaktadır.', 201);
			}
			$output = Command::runSudo(
				"sh -c \"sed -i '/\[{:hostsname}\]/a {:clientLine}' {:hostsFilePath}\"",
				[
					'hostsname' => $hostsname,
					'clientLine' => $clientLine,
					'hostsFilePath' => $this->hostsFilePath
				]
			);
		}
		if (trim($output) == '') {
			return respond('Başarıyla Eklendi', 200);
		} else {
			return respond($output, 201);
		}
	}

	function addGroup()
	{
		$groupname = trim(request('groupname'));

		$textHostFile = Command::runSudo(
			'ansible-inventory -i {:hostsFilePath} --list --yaml',
			[
				'hostsFilePath' => $this->hostsFilePath
			]
		);
		$arrayHosts = yaml_parse($textHostFile)['all']['children'];

		if (array_key_exists($groupname, $arrayHosts)) {
			return respond('Böyle bir grup bulunmaktadır.', 201);
		}

		$output = Command::runSudo(
			"sh -c 'echo \"\n[{:groupname}]\n\"  >> {:hostsFilePath}'",
			[
				'groupname' => $groupname,
				'hostsFilePath' => $this->hostsFilePath
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
		$hostsname = trim(request('deletehostsname'));
		$ipaddress = trim(request('ipaddress'));
		$ansibleSshUser = request('ansibleSshUser');
		if ($ansibleSshUser == '-') {
			$clientLine = $ipaddress;
		} else {
			$clientLine = "$ipaddress ansible_ssh_user=$ansibleSshUser";
		}

		if ($hostsname == 'Grupsuz') {
			$linenumber = Command::runSudo(
				"cat {:hostsFilePath} | grep -n -v '^#' | sed -n -e '/\[.*/,/\\$/!p' | grep @{:clientLine} | cut -d ':' -f1",
				[
					'hostsfilepath' => $this->hostsFilePath,
					'clientLine' => $clientLine
				]
			);
		} else {
			$linenumber = Command::runSudo(
				"cat -n {:hostsFilePath} | sed -n -e '/\[{:hostsname}/,/\[/ p' | grep @{:clientLine} | awk '{print $1}'",
				[
					'hostsFilePath' => $this->hostsFilePath,
					'hostsname' => $hostsname,
					'clientLine' => $clientLine
				]
			);
		}

		if ($linenumber == '') {
			return respond('Hata Oluştu', 201);
		}

		$output = Command::runSudo(
			"sh -c \"sed -i '{:linenumber} d' {:hostsFilePath}\"",
			[
				'hostsFilePath' => $this->hostsFilePath,
				'linenumber' => $linenumber
			]
		);

		if (trim($output) == '') {
			return respond('Başarıyla Silindi', 200);
		} else {
			return respond($output, 201);
		}
	}

	public static function getUserSelect()
	{
		$userFilePath = '/etc/ansible/users';
		$userFileText = str_replace(
			"\n",
			'',
			Command::runSudo('cat {:userFilePath}', [
				'userFilePath' => $userFilePath
			])
		);
		$userArray = json_decode($userFileText, true);
		$userNameArray = [];
		if (!empty($userArray)) {
			foreach ($userArray as $key => $value) {
				array_push($userNameArray, $value['name']);
			}
		}
		return collect($userNameArray)
			->map(function ($i) {
				return ['name' => $i];
			}, $userNameArray)
			->pluck('name', 'name')
			->toArray();
	}

	function addShhKey()
	{
		$ipAddress = request('ipAddress');
		$sshUserName = request('sshUserName');
		$sshUserPass = request('sshUserPass');
		$checkKey = (bool) Command::runSudo(
			'[ -f ~/.ssh/id_rsa.pub ] && echo 1 || echo 0'
		);

		if (!$checkKey) {
			Command::run('mkdir ~/.ssh');
			Command::run('chmod 700 ~/.ssh');
			Command::run('ssh-keygen -b 2048 -t rsa -f ~/.ssh/id_rsa -q -N ""');
		}
		$sshKey = Command::runSudo('cat ~/.ssh/id_rsa.pub') . "\n";

		SSHEngine::init($ipAddress, $sshUserName, $sshUserPass);
		Command::bindEngine(SSHEngine::class);
		$checkDir = (bool) Command::runSudo(
			'[ -d ~/.ssh ] && echo 1 || echo 0'
		);

		if (!$checkDir) {
			Command::run('mkdir ~/.ssh');
			Command::run('chmod 700 ~/.ssh');
			Command::run('touch ~/.ssh/authorized_keys');
			Command::run('chmod 600 ~/.ssh/authorized_keys');
		}

		$sshKeyCheck = Command::run(
			'cat ~/.ssh/authorized_keys | grep -e @{:sshKey} 1>/dev/null 2>/dev/null && echo 1 || echo 0',
			[
				'sshKey' => $sshKey
			]
		);

		if ($sshKeyCheck == '1') {
			return respond('Ssh key zaten bulunmaktadır', 201);
		}

		Command::run(
			"bash -c \"echo @{:sshKey} | base64 -d | tee -a  ~/.ssh/authorized_keys\"",
			[
				'sshKey' => base64_encode($sshKey)
			]
		);

		return respond('Eklendi', 200);
	}

	function removeShhKey()
	{
		$ipAddress = request('ipAddress');
		$sshUserName = request('sshUserName');
		$sshUserPass = request('sshUserPass');
		$sshKey = Command::runSudo('cat ~/.ssh/id_rsa.pub');

		SSHEngine::init($ipAddress, $sshUserName, $sshUserPass);
		Command::bindEngine(SSHEngine::class);

		Command::runSudo(
			"grep -v \"{:sshKey}\" ~/.ssh/authorized_keys > ~/.ssh/temp; mv ~/.ssh/temp ~/.ssh/authorized_keys",
			[
				'sshKey' => $sshKey
			]
		);

		$sshKeyCheck = Command::runSudo(
			'cat ~/.ssh/authorized_keys | grep -e @{:sshKey} 1>/dev/null 2>/dev/null && echo 1 || echo 0',
			[
				'sshKey' => $sshKey
			]
		);

		if ($sshKeyCheck == '0') {
			return respond('Silindi', 200);
		} else {
			return respond('Hata oluştu', 201);
		}
	}
}

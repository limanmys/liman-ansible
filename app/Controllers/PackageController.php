<?php

namespace App\Controllers;

use Liman\Toolkit\OS\Distro;

class PackageController
{
	public static function verifyInstallation()
	{
		$checkAnsible = (bool) Distro::debian(
			"dpkg --get-selections | grep -v deinstall | awk '{print $1}' |  grep '^ansible$' 2>/dev/null  1>/dev/null && echo 1 || echo 0"
		)
			->centos(
				"rpm -qa | grep '^ansible'  2>/dev/null  1>/dev/null && echo 1 || echo 0"
			)
			->runSudo();

		if ($checkAnsible) {
			return true;
		} else {
			return false;
		}
	}
	
	function install()
	{
		return respond(
			view('task', [
				'onFail' => 'onTaskFail',
				'onSuccess' => 'onTaskSuccess',
				'tasks' => [
					0 => [
						'name' => 'InstallPackage',
						'attributes' => []
					]
				]
			]),
			200
		);
	}
}

<?php

namespace App\Controllers;
use App\Utils\Command\Command;

class PackageController
{
	public static function verifyInstallation()
	{
		if (
			trim(
				Command::runSudo(
					'dpkg -s ansible | grep "Status" | grep -w "install" 1>/dev/null 2>/dev/null && echo "1" || echo "0"'
				)
			) == '1'
		) {
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

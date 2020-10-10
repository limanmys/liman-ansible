<?php

namespace App\Controllers;
use App\Utils\Command\Command;

class PackageController
{
	public static function verifyInstallation()
	{
		if (trim(Command::runSudo('dpkg -s ansible | grep "Status" | grep -w "install" 1>/dev/null 2>/dev/null && echo "1" || echo "0"')) == "1") {
        	return true;
		} else {
			return false;
		}
	}
	function install()
	{
		$command = "bash -c 'DEBIAN_FRONTEND=noninteractive apt install ansible -qqy >/tmp/limanLog 2>&1 & disown'";
		Command::runSudo($command);
		return respond("started", 200);
	}

	function observeInstallation()
	{
		if ($this->verifyInstallation()) {
			return respond(navigate(''), 300);
		}
		$log = Command::runSudo("cat /tmp/limanLog");
		return respond($log, 200);
	}
}

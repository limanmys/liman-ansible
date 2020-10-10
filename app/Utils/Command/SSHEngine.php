<?php
namespace App\Utils\Command;

use App\Helpers\Formatter;
use Exception;
use phpseclib\Net\SSH2;

class SSHEngine implements ICommandEngine
{
	private static $connection;
	private static $password;
	private static $initialized = false;

	public static function run($command)
	{
		if (!self::$initialized) {
			throw new Exception(
				'The SSHEngine class must ve initialized with init() function.'
			);
		}
		return self::$connection->exec($command);
	}

	public static function sudo()
	{
		return Formatter::run(
			'echo :password | base64 -d | sudo -S -p " " id 2>/dev/null 1>/dev/null; sudo ',
			['password' => base64_encode(self::$password . "\n")]
		);
	}

	public static function init($hostname, $username, $password)
	{
		self::$password = $password;
		$connection = new SSH2($hostname);
		if (!$connection->login($username, $password)) {
			throw new Exception(
				$connection->isConnected()
					? __(
						'Kullanıcı adı ve şifreniz ile sunucuya giriş yapılamadı!'
					)
					: __('İstemciye bağlanılamadı!')
			);
		}
		self::$connection = $connection;
		self::$initialized = true;
	}
}

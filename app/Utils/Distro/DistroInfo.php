<?php
namespace App\Utils\Distro;

use App\Utils\Command\Command;
use Dotenv\Dotenv;

class DistroInfo
{
	public $distroID;
	public $versionID;
	public $base;
	public $pretty;
	public $majorVersion;
	public $minorVersion;
	public $slug;
	public $majorSlug;

	public function __construct()
	{
		$this->parseEnv();
	}

	private function parseEnv()
	{
		$release = Command::run('cat /etc/os-release | grep =');
		$env = Dotenv::parse($release);
		$this->distroID = strtolower(trim($env['ID']));
		$this->versionID = strtolower(trim($env['VERSION_ID']));
		$this->base = strtolower(trim($env['ID_LIKE']));
		$this->pretty = trim($env['PRETTY_NAME']);
		$this->parseVersion();
		$this->parseSlug();
	}

	private function parseVersion()
	{
		$version = explode('.', $this->versionID);
		$this->majorVersion = $version[0];
		$this->minorVersion = $version[1];
	}

	private function parseSlug()
	{
		$this->slug =
			$this->distroID . $this->majorVersion . $this->minorVersion;
		$this->majorSlug = $this->distroID . $this->majorVersion;
	}
}

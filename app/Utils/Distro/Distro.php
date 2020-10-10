<?php
namespace App\Utils\Distro;

use App\Utils\Command\Command;

class Distro
{
	private $options = [];
	private static $distroInfo = null;
	private static $currentEngine = null;
	private $default = false;

	public function __construct()
	{
		self::getDistro();
	}

	public static function getDistro()
	{
		if (
			self::$distroInfo == null ||
			self::$currentEngine != Command::getEngine()
		) {
			self::$distroInfo = new DistroInfo();
			self::$currentEngine = Command::getEngine();
		}
		return self::$distroInfo;
	}

	public function get()
	{
		$distro = self::getDistro();
		if (isset($this->options[$distro->slug])) {
			return $this->options[$distro->slug];
		} elseif (isset($this->options[$distro->majorSlug])) {
			return $this->options[$distro->majorSlug];
		} elseif (isset($this->options[$distro->distroID])) {
			return $this->options[$distro->distroID];
		} elseif (isset($this->options[$distro->base])) {
			return $this->options[$distro->base];
		}
		return $this->default;
	}

	public function run($attributes = [])
	{
		$result = $this->get();
		if (!$result) {
			return false;
		}
		return Command::run($result, $attributes);
	}

	public function runSudo($attributes = [])
	{
		$result = $this->get();
		if (!$result) {
			return false;
		}
		return Command::runSudo($result, $attributes);
	}

	public function default($default)
	{
		$this->default = $default;
		return $this;
	}

	public function __call($method, $parameters)
	{
		$this->options[$method] = $parameters[0];
		return $this;
	}

	public static function __callStatic($method, $parameters)
	{
		return (new self())->$method(...$parameters);
	}
}

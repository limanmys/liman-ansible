<?php
namespace App\Helpers;

//credits: dkellner from stackoverflow https://stackoverflow.com/a/51302725
class Route
{
	private static $store = [];
	private static $maker = '';
	private static $declaration = '
        function %s() {
            return call_user_func_array(
                %s::get(__FUNCTION__),
                func_get_args()
            );
        }
    ';

	private static function safeName($name)
	{
		$name = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
		$name = substr($name, 0, 64);
		return $name;
	}

	public static function add($name, $func)
	{
		$name = self::safeName($name);
		self::$store[$name] = $func;
		self::$maker .= sprintf(self::$declaration, $name, __CLASS__);
	}

	public static function get($name)
	{
		return self::$store[$name];
	}

	public static function make()
	{
		return self::$maker;
	}
}

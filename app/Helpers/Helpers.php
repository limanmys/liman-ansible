<?php

use App\Helpers\Validator;

if (!function_exists('validate')) {
	function validate($rules)
	{
		$validator = (new Validator())->make(request(), $rules);
		if ($validator->fails()) {
			$errors = $validator->errors();
			abort($errors->first(), 400);
		}
	}
}

if (!function_exists('checkPort')) {
	function checkPort($ip, $port)
	{
		restoreHandler();
		if ($port == -1) {
			return true;
		}
		$fp = @fsockopen($ip, $port, $errno, $errstr, 0.1);
		setHandler();
		if (!$fp) {
			return false;
		} else {
			fclose($fp);
			return true;
		}
	}
}

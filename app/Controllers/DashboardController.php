<?php

namespace App\Controllers;

use Liman\Toolkit\Shell\Command;

class DashboardController
{
	function get()
	{
		$output = Command::runSudo('ansible --version');
		$outputArray = explode("\n", $output);
		$data = [];
		foreach ($outputArray as $key => $value) {
			if ($key == 0) {
				$partItem = explode(' ', $value);
			} else {
				$partItem = explode('=', $value);
			}
			$item = [
				'name' => trim($partItem[0]),
				'value' => trim($partItem[1])
			];
			array_push($data, $item);
		}
		return view('components.json-table', [
			'data' => $data
		]);
	}
}

<?php

namespace App\Controllers;

class HomeController
{
	function index()
	{
		return view('index');
	}

	function install()
	{
		return view('install');
	}
}

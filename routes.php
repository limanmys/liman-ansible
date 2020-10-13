<?php

return [
    "index" => "HomeController@index",

    "get_dashboard" => "DashboardController@get",

    "get_hosts" => "HostsController@get",
    "add_host" => "HostsController@add",
    "add_group" => "HostsController@addGroup",
    "delete_ip" => "HostsController@delete",

    "get_users" => "UserController@get",
    "add_user" => "UserController@add",
    "delete_user" => "UserController@delete",

    "get_files" => "FileController@get",
    "get_file_content" => "FileController@getContent",
    "upload_file" => "FileController@upload",

    "install_package" => "PackageController@install",
    "observe_installation" => "PackageController@observeInstallation",

];

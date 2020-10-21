<?php

return [
    "index" => "HomeController@index",

    "get_dashboard" => "DashboardController@get",

    "get_hosts" => "HostsController@get",
    "add_host" => "HostsController@add",
    "add_group" => "HostsController@addGroup",
    "delete_ip" => "HostsController@delete",
    "get_host_content" => "HostsController@getContent",
    "add_ssh_key" => "HostsController@addShhKey",

    "get_users" => "UserController@get",
    "add_user" => "UserController@add",
    "delete_user" => "UserController@delete",

    "get_files" => "FileController@get",
    "get_file_content" => "FileController@getContent",
    "upload_file" => "FileController@upload",
    "edit_file" => "FileController@edit",

    "install_package" => "PackageController@install",
    "observe_installation" => "PackageController@observeInstallation",

    "get_playbooks" => "PlaybookController@get",
    "get_content_playbook" => "PlaybookController@getContent",
    "edit_playbook" => "PlaybookController@edit",
    "delete_playbook" => "PlaybookController@delete",
    "create_playbook" => "PlaybookController@create",
    "run_playbook" => "PlaybookController@run",

    "runTask" => "TaskController@runTask",
    "checkTask" => "TaskController@checkTask",
];

<?php

return [
    "index" => "HomeController@index",
    "install" => "HomeController@install",

    "get_dashboard" => "DashboardController@get",

    "get_hosts" => "HostsController@get",
    "add_host" => "HostsController@add",
    "add_group" => "HostsController@addGroup",
    "delete_ip" => "HostsController@delete",
    "get_host_content" => "HostsController@getContent",
    "add_ssh_key" => "HostsController@addShhKey",
    "remove_ssh_key" => "HostsController@removeShhKey",
    "delete_group" => "HostsController@deleteGroup",

    "get_users" => "UserController@get",
    "add_user" => "UserController@add",
    "delete_user" => "UserController@delete",

    "get_files" => "FileController@get",
    "get_file_content" => "FileController@getContent",
    "upload_file" => "FileController@upload",
    "edit_file" => "FileController@edit",

    "install_package" => "PackageController@install",

    "get_playbooks" => "PlaybookController@get",
    "get_content_playbook" => "PlaybookController@getContent",
    "edit_playbook" => "PlaybookController@edit",
    "delete_playbook" => "PlaybookController@delete",
    "create_playbook" => "PlaybookController@create",
    "run_playbook" => "PlaybookController@run",
    "playbook_save_output" => "PlaybookController@saveLog",

    "runTask" => "TaskController@runTask",
    "checkTask" => "TaskController@checkTask",

    "get_logs" => "LogController@get",
    "get_content_log" => "LogController@getContent",
    "delete_log" => "LogController@delete",

];

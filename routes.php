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
    "add_ssh_key_request" => "HostsController@addSshKeyRequest",
    "remove_ssh_key" => "HostsController@removeShhKey",
    "delete_group" => "HostsController@deleteGroup",

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

    "get_templates" => "TemplateController@get",
    "create_template" => "TemplateController@create",
    "delete_template" => "TemplateController@delete",
    "get_content_template" => "TemplateController@getContent",
    "edit_template" => "TemplateController@edit",

    "runTask" => "TaskController@runTask",
    "checkTask" => "TaskController@checkTask",

    "get_logs" => "LogController@get",
    "get_content_log" => "LogController@getContent",
    "delete_log" => "LogController@delete",

    "list_hosts" => "Playbook2Controller@listHosts",
    "playbook2_save_output" => "Playbook2Controller@saveLog",
    "get_playbooks2" => "Playbook2Controller@get"

];

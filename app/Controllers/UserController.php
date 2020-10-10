<?php

namespace App\Controllers;
use App\Utils\Command\Command;

class UserController
{
    protected $userFilePath = "/etc/ansible/users";

	function get()
	{
        $arrayJson = [];
        $fileCheck = Command::runSudo(" [ -f {:userFilePath} ] 2>/dev/null 1>/dev/null && echo 1 || echo 0",[
            "userFilePath" => $this->userFilePath,
        ]);
        if ($fileCheck == "0") {
            return respond("notfoundfile", 202);
        }
        $textJson = str_replace("\n", "", Command::runSudo("cat {:userFilePath}",[ "userFilePath" => $this->userFilePath]));
        $textJson = substr($textJson, 0, -1);
        $arrayJson = json_decode("[" . $textJson . "]", true);
        if (!is_array($arrayJson)) {
            return respond("error", 201);
        }
        return view('table', [
            "value" => $arrayJson,
            "title" => [
                "İsim", "Password", "Sudo Yetkisi"
            ],
            "display" => [
                "name", "password", "sudo"
            ],
        ]);
    }
    
    function add(){

        $username = request("username");
        $password = request("password");
        $permission = request("type");

        $fileCheck = Command::runSudo(" [ -f {:userFilePath} ] 2>/dev/null 1>/dev/null && echo 1 || echo 0",[
            "userFilePath" => $this->userFilePath,
        ]);

        if ($fileCheck == "0") {
            Command::runSudo("touch {:userFilePath}",[ "userFilePath" => $this->userFilePath]);
        }

        $textJson = str_replace("\n", "",  Command::runSudo("cat {:userFilePath}",[ "userFilePath" => $this->userFilePath]));
        $textJson = substr($textJson, 0, -1);
        $arrayJson = json_decode("[" . $textJson . "]", true);

        foreach ($arrayJson as $key => $value) {
            if ($value["name"] == trim($username) && $value["password"] == trim($password)) {
                return respond("Aynı kullanıcı bulunmaktadır", 201);
            }
        }
        $item = array(
            "name" => $username,
            "password" => $password,
            "sudo" => $permission
        );
        $text = json_encode($item);
        $text = str_replace("\"", "\\\"", $text);
        $output =  Command::runSudo("sh -c 'echo $text, >> {:userFilePath}'",["userFilePath" => $this->userFilePath]);
        if (trim($output) == "") {
            return respond("Başarıyla Eklendi", 200);
        } else {
            return respond($output, 201);
        }
    }
}

<?php

namespace App\Controllers;
use App\Utils\Command\Command;

class FileController
{
    function get()
    {
        $output = runCommand("ls /opt/varlik");
        if (Command::runSudo(" [ -d /opt/varlik ] 2>/dev/null 1>/dev/null && echo 1 || echo 0") == "0" || trim($output) == "") {
            return respond("notfoundfile", 202);
        }
        $result = runScript("jstree.py", "", true);
        return respond($result, 200);
    }

    function getContent()
    {
        $filePath = request("filepath");
        $output = Command::runSudo("cat {:filePath}",["filePath" => $filePath]);
        return respond($output, 200);
    }
}

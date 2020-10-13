<?php

namespace App\Controllers;
use App\Utils\Command\Command;

class FileController
{
    function get()
    {
        $output = Command::runSudo("ls /opt/varlik");
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

    function upload()
    {
        $dirName = request("dirName");
        $name = request('name');
		$path = request('path');
        $remotePath = '/tmp/' . str_replace(' ', '', $name);
        $path_parts = pathinfo($path);
        
        $fileCheck = Command::runSudo(" [ -d /opt/varlik/{:dirName} ] 2>/dev/null 1>/dev/null && echo 1 || echo 0",[
            "dirName" => $dirName
        ]);

        if ($fileCheck == "1")  {
            return respond("Klasör bulunmaktadır.",201);
        }

        Command::runSudo("mkdir /opt/varlik/{:dirName}",[
            "dirName" => $dirName
        ]);
        
        $output = putFile(getPath($path), $remotePath);

		if ($output !== 'ok') {
			return respond('Dosya Yükleme Başarısız', 201);
        }

        if($path_parts['extension'] == "gz"){
            Command::runSudo("tar -xzf {:remotePath} -C /opt/varlik/{:dirName}",[
                "dirName" => $dirName,
                "remotePath" => $remotePath
            ]);
        }else if($path_parts['extension'] == "tar"){
            Command::runSudo("tar -xf {:remotePath} -C /opt/varlik/{:dirName}",[
                "dirName" => $dirName,
                "remotePath" => $remotePath
            ]);
        }else if($path_parts['extension'] == "zip"){
            $checkPackage = Command::runSudo("apt list --installed 2>/dev/null | grep 'unzip' 1>/dev/null 2>/dev/null && echo 1 || echo 0 ");
            if($checkPackage == "0"){
                Command::runSudo("rm -rf /opt/varlik/{:dirName}",[
                    "dirName" => $dirName,
                ]);
                return respond("Bu dosya türü için unzip kurulu olmalıdır",201);
            }else{
                Command::runSudo("unzip {:remotePath} -d /opt/varlik/{:dirName}",[
                    "dirName" => $dirName,
                    "remotePath" => $remotePath
                ]);
            }
        }else{
            return respond("Desteklenmeyen dosya tipi.",201);
        } 
        
        $pathFile = str_replace(' ', '\ ', getPath(quotemeta($path)));
        shell_exec("rm -rf $pathFile");
        
        Command::runSudo("rm -rf  {:remotePath}",[
            "remotePath" => $remotePath
        ]);
        
        return respond("Başarılı",200);

    }
}

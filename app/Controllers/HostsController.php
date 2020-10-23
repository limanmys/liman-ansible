<?php

namespace App\Controllers;

use App\Utils\Command\Command;
use App\Utils\Command\SSHEngine;

class HostsController
{
    protected $hostsfilepath = "/etc/ansible/hosts";

    function get(){
		$output = Command::runSudo("cat {:hostsfilepath} | grep -v '^#'", [
            "hostsfilepath" => $this->hostsfilepath,
		]);
        preg_match_all('/\[(.*)\]/', $output, $matches);
        $hostNameArray = collect($matches[1])
			->map(function ($i) {
				return ['name' => $i];
			}, $matches[1])
            ->toArray();
        return view('table', [
            'value' => $hostNameArray,
            'title' => ['Host Adı'],
            'display' => ['name'],
            'menu' => [
                'Gör' => [
                    'target' => 'getHostsContent',
                    'icon' => 'fa-eye',
                ],
            ]
        ]);
    }

    function getContent()
    {
        $hostName = request("hostName");
        $output = Command::runSudo("cat {:hostsfilepath} | grep -v '^#'", [
            "hostsfilepath" => $this->hostsfilepath,
        ]) . " [";

        $output = str_replace("\n", "---", $output);
        preg_match('/\['.$hostName.'\s*](.*?)(?=\[)/', $output, $matches);
        $lines = explode("---",$matches[1]);
        $lines = array_filter($lines);
        $data = [];
        foreach ($lines as $key => $line) {
            $lineParts = explode(" ",trim($line));
            sizeof($lineParts) == 1  ? $sshUser = "-" : $sshUser = trim(explode("=",$lineParts[1])[1]) ;
            sizeof($lineParts) == 1  ? $sshPass = "-" : $sshPass = trim(explode("=",$lineParts[2])[1]) ;
            $data[]= [
                "ip" => $lineParts[0],
                "ssh_user" => $sshUser,
                "ssh_pass" => $sshPass,
            ];
        }

        return view('table', [
            'value' => $data,
            'title' => ['Ip Adresi','Ssh Kullanıcı','Ssh Şifre'],
            'display' => ['ip','ssh_user','ssh_pass'],
            'menu' => [
                'Ssh Key Ekle' => [
                    'target' => 'openAddSshKeyComponent',
                    'icon' => 'fa-plus',
                ],
                'Sil' => [
                    'target' => 'deleteClientIpJS',
                    'icon' => 'fa-trash',
                ],
            ]
        ]);
    }

    function add()
    {
        validate([
			'ipaddress' => 'required|string'
        ]);
        
        $hostsname = request("hostsname");
        $ipaddress = request("ipaddress");
        $ansibleSshUser = request("ansibleSshUser");
        $ansibleSshPass = request("ansibleSshPass");
        if($ansibleSshUser == ""){
            $clientLine = $ipaddress;
        }else{
            $clientLine = "$ipaddress ansible_ssh_user=$ansibleSshUser ansible_ssh_pass=$ansibleSshPass";
        }

        if ($hostsname == "Grupsuz") {
            $grupsuz = Command::runSudo("cat {:hostsfilepath} | grep -v '^#' | sed -n -e '/\[.*/,/\\$/!p'", [
                "hostsfilepath" => $this->hostsfilepath,
            ]);
            $grupsuz = explode("\n", $grupsuz);
            if (in_array($clientLine, $grupsuz)) {
                return respond("Böyle bir client bulunmaktadır.", 201);
            }
            $linenumber = Command::runSudo("cat {:hostsfilepath} | grep -n -v '^#' | sed -n -e '/\[.*/,/\\$/!p' | tail -n 1 | cut -d ':' -f1", [
                "hostsfilepath" => $this->hostsfilepath,
            ]);

            $output = Command::runSudo("sh -c \"sed -i '{:linenumber} i {:ipaddress}' {:hostsfilepath}\"", [
                "linenumber" => $linenumber,
                "ipaddress" => $ipaddress,
                "hostsfilepath" => $this->hostsfilepath
            ]);
        } else {
            $output = Command::runSudo("cat {:hostsfilepath} | grep -v '^#'", [
                "hostsfilepath" => $this->hostsfilepath,
            ]) . " [";
            $output = str_replace("\n", " ", $output);
            preg_match("/\[$hostsname\](.*?)(?=\[)/", $output, $hostzone);
            if (strpos($hostzone[1], $clientLine) !== FALSE) {
                return respond("Böyle bir client bulunmaktadır.", 201);
            }
           $output = Command::runSudo("sh -c \"sed -i '/\[{:hostsname}\]/a {:clientLine}' {:hostsfilepath}\"", [
                "hostsname" => $hostsname,
                "clientLine" => $clientLine,
                "hostsfilepath" => $this->hostsfilepath
            ]); 
        }
        if (trim($output) == "") {
            return respond("Başarıyla Eklendi", 200);
        } else {
            return respond($output, 201);
        }
    }

    function addGroup()
    {
        $groupname = trim(request("groupname"));
        $ipaddress = request("ipaddress");
        $ansibleSshUser = request("ansibleSshUser");
        $ansibleSshPass = request("ansibleSshPass");
        if($ansibleSshUser == ""){
            $clientLine = $ipaddress;
        }else{
            $clientLine = "$ipaddress ansible_ssh_user=$ansibleSshUser ansible_ssh_pass=$ansibleSshPass";
        }

        if (!filter_var(trim($ipaddress), FILTER_VALIDATE_IP)) {
            return respond("Geçerli ip adresi giriniz", 201);
        }
        $allgroupnametext = Command::runSudo("cat {:hostsfilepath} | grep -v '^#'", [
            "hostsfilepath" => $this->hostsfilepath,
        ]) . " [";

        $allgroupnametext = str_replace("\n", " ", $allgroupnametext);
        preg_match_all("/\[(.*?)\]/", $allgroupnametext, $allgroupname);
        if (in_array($groupname, $allgroupname[1])) {
            return respond("Böyle bir grup bulunmaktadır.", 201);
        }
        $output = Command::runSudo("sh -c 'echo \"\n[{:groupname}]\n{:clientLine}\"  >> {:hostsfilepath}'", [
            "groupname" => $groupname,
            "clientLine" => $clientLine,
            "hostsfilepath" => $this->hostsfilepath,
        ]);


        if (trim($output) == "") {
            return respond("Başarıyla Eklendi", 200);
        } else {
            return respond($output, 201);
        }
    }

    function delete()
    {
        $hostsname = trim(request("deletehostsname"));
        $ipaddress = trim(request("ipaddress"));
        $ansibleSshUser = request("ansibleSshUser");
        $ansibleSshPass = request("ansibleSshPass");
        if($ansibleSshUser == "-"){
            $clientLine = $ipaddress;
        }else{
            $clientLine = "$ipaddress ansible_ssh_user=$ansibleSshUser ansible_ssh_pass=$ansibleSshPass";
        }
        

        if ($hostsname == "Grupsuz") {
            $linenumber = Command::runSudo("cat {:hostsfilepath} | grep -n -v '^#' | sed -n -e '/\[.*/,/\\$/!p' | grep @{:clientLine} | cut -d ':' -f1", [
                "hostsfilepath" => $this->hostsfilepath,
                "clientLine" => $clientLine,
            ]);
        } else {
            $linenumber = Command::runSudo("cat -n {:hostsfilepath} | sed -n -e '/\[{:hostsname}/,/\[/ p' | grep @{:clientLine} | awk '{print $1}'", [
                "hostsfilepath" => $this->hostsfilepath,
                "hostsname" =>  $hostsname,
                "clientLine" => $clientLine,
            ]);
        }
        
        if($linenumber == ""){
            return respond("Hata Oluştu",201);
        }

        $output = Command::runSudo("sh -c \"sed -i '{:linenumber} d' {:hostsfilepath}\"", [
            "hostsfilepath" => $this->hostsfilepath,
            "linenumber" => $linenumber,
        ]);

        if (trim($output) == "") {
            return respond("Başarıyla Silindi", 200);
        } else {
            return respond($output, 201);
        }
    }

    public static function getUserSelect(){
        $userFilePath = "/etc/ansible/users";
        $userFileText = str_replace("\n", "", Command::runSudo("cat {:userFilePath}",[ "userFilePath" => $userFilePath]));
        $userArray = json_decode($userFileText , true);
        $userNameArray = [];
        if(!empty($userArray)){
            foreach ($userArray as $key => $value) {
                array_push($userNameArray,$value["name"]);
            }
        }
        return collect($userNameArray)
                ->map(function ($i) {
                    return ['name' => $i];
                }, $userNameArray)
                ->pluck('name', 'name')
                ->toArray();
    }

    function addShhKey(){
        $userFilePath = "/etc/ansible/users";
        $ipAddress = request("ipAddress");
        $username = request("username");
        $userFileText = str_replace("\n", "", Command::runSudo("cat {:userFilePath}",[ "userFilePath" => $userFilePath]));
        $userArray = json_decode($userFileText , true);

        foreach ($userArray as $key => $value) {
            if($value["name"] == $username){
                $password = $value["password"];
            }
        }

        $sshKey = Command::runSudo("cat ~/.ssh/id_rsa.pub");

        SSHEngine::init($ipAddress, $username, $password);
        Command::bindEngine(SSHEngine::class);
        $sshKeyCheck = Command::runSudo("cat /home/akbel/.ssh/authorized_keys | grep -e @{:sshKey} 1>/dev/null 2>/dev/null && echo 1 || echo 0",[
            'sshKey' => $sshKey,
        ]);
        
        if($sshKeyCheck == "1"){
            return respond("Ssh key zaten bulunmaktadır",201);
        }

        Command::run(
			"bash -c \"echo @{:sshKey} | base64 -d | tee -a  ~/.ssh/authorized_keys\"",
			[
				'sshKey' => base64_encode($sshKey),
			]
		);
        
        return respond("Eklendi",200);
    }
}

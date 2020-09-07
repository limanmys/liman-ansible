<?php

    $hostsfilepath = "/etc/ansible/hosts";
    $userfilepath = "/etc/ansible/users";

    function index(){
        return view('index');
    }

    function install(){
        return view('install');
    }

    function getUsers(){
        global $userfilepath;

        $textJson = str_replace("\n","",runCommand("cat $userfilepath"));
        $textJson = substr($textJson, 0, -1);
        $arrayJson = json_decode("[".$textJson."]",true);
        if(!is_array($arrayJson)){
            return respond("error",201);
        }
        return view('table', [
            "value" => $arrayJson,
            "title" => [
                "İsim","Password","Sudo Yetkisi"
            ],
            "display" => [
                "name","password","sudo"
            ],
        ]);
    }

    function getHosts(){
        global $hostsfilepath;
        $output = trim(runCommand("cat $hostsfilepath | grep -v '^#'")) . " [";
        $output = str_replace("\n"," ",$output);
        preg_match_all('/\[.*?(?=\[)/',$output, $matches);
        $data = [];
        foreach ($matches[0] as $key => $value) {
            preg_match('/\[(.*)\]/',$value,$name);
            preg_match('/](.*)/',$value,$ip);

            if(strpos(trim($ip[1]),' ') !== FALSE){
                $ips = explode(" ",trim($ip[1]));
            }else{
                $ips[0] = $ip[1];
            }
            $item = array(
                "name" => $name[1],
                "ip" => $ips,
            );
            array_push($data,$item);
        }
        return view('hosts', ['data' => $data]);
    }
    
    function installAnsiblePackage()
    {
        $command = sudo() . "bash -c 'DEBIAN_FRONTEND=noninteractive apt install ansible -qqy >/tmp/limanLog 2>&1 & disown'";
        runCommand($command);
        return respond("started",200);
    }

    function observeInstallation()
    {
        if(verifyInstallation()){
            return respond(navigate(''),300);
        }
        $log = runCommand(sudo() . "cat /tmp/limanLog");
        return respond($log,200);
    }


    function verifyInstallation(){
        if(trim(runCommand('dpkg -s ansible | grep "Status" | grep -w "install" 1>/dev/null 2>/dev/null && echo "1" || echo "0"')) == "1"){
            return true;
        }else{
            return false;
        }
    }
?>
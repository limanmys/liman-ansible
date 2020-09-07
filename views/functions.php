<?php

    function index(){
        return view('index');
    }

    function install(){
        return view('install');
    }

    function getHosts(){
        return respond("sa",200);
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
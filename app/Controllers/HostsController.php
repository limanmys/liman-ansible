<?php

namespace App\Controllers;

use App\Utils\Command\Command;

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

    function getHostContent()
    {
        $hostName = request("hostName");
        $output = Command::runSudo("cat {:hostsfilepath} | grep -v '^#'", [
            "hostsfilepath" => $this->hostsfilepath,
        ]) . " [";
        $output = str_replace("\n", " ", $output);
        preg_match('/\['.$hostName.'\s*](.*?)(?=\[)/', $output, $matches);

        $ips = trim($matches[1]);

        if ($ips == "") {
            $ips = [];
        } else if (strpos($ips, ' ') !== FALSE) {
            $ips = explode(" ", $ips);
        } else {
            $ips[0] = $ips;
        }

        $ipArray = collect($ips)
			->map(function ($i) {
				return ['ip' => $i];
            }, $ips)
            ->toArray();
            
        return view('table', [
            'value' => $ipArray,
            'title' => ['Ip Adresi'],
            'display' => ['ip'],
            'menu' => [
                'Sil' => [
                    'target' => 'deleteClientIpJS',
                    'icon' => 'fa-trash',
                ],
            ]
        ]);
    }

    function add()
    {
        $hostsname = request("hostsname");
        $ipaddress = request("ipaddress");
        if ($hostsname == "Grupsuz") {
            $grupsuz = Command::runSudo("cat {:hostsfilepath} | grep -v '^#' | sed -n -e '/\[.*/,/\\$/!p'", [
                "hostsfilepath" => $this->hostsfilepath,
            ]);
            $grupsuz = explode("\n", $grupsuz);
            if (in_array($ipaddress, $grupsuz)) {
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
            if (strpos($hostzone[1], trim($ipaddress)) !== FALSE) {
                return respond("Böyle bir client bulunmaktadır.", 201);
            }
            $output = Command::runSudo("sh -c \"sed -i '/\[{:hostsname}\]/a {:ipaddress}' {:hostsfilepath}\"", [
                "hostsname" => $hostsname,
                "ipaddress" => $ipaddress,
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
        $output = Command::runSudo("sh -c 'echo \"\n[{:groupname}]\n{:ipaddress}\"  >> {:hostsfilepath}'", [
            "groupname" => $groupname,
            "ipaddress" => $ipaddress,
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
        if ($hostsname == "Grupsuz") {
            $linenumber = Command::runSudo("cat {:hostsfilepath} | grep -n -v '^#' | sed -n -e '/\[.*/,/\\$/!p' | grep @{:ipaddress} | cut -d ':' -f1", [
                "hostsfilepath" => $this->hostsfilepath,
                "ipaddress" => $ipaddress,
            ]);
        } else {
            $linenumber = Command::runSudo("cat -n {:hostsfilepath} | sed -n -e '/\[{:hostsname}/,/\[/ p' | grep @{:ipaddress} | awk '{print $1}'", [
                "hostsfilepath" => $this->hostsfilepath,
                "hostsname" =>  $hostsname,
                "ipaddress" => $ipaddress,
            ]);
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
}

<?php 

    $checkPackage = verifyInstallation();
    if(!$checkPackage){ 
    echo "<script>window.location.href = '" . navigate('install') . "';</script>";
    } 


?>

<ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px;">
    <li class="nav-item">
        <a class="nav-link active"  onclick="getHosts()" href="#hosts" data-toggle="tab">Hosts</a>
    </li>
    <li class="nav-item">
        <a class="nav-link "  onclick="getUsers()" href="#users" data-toggle="tab">Kişiler</a>
    </li>
    <li class="nav-item">
        <a class="nav-link "  onclick="getFiles()" href="#varlik" data-toggle="tab">Varlık</a>
    </li>
</ul>

<div class="tab-content">
    @include('hosts')  
      
    <div id="users" class="tab-pane">
        @include('modal-button',[
            "class"     =>  "btn btn-outline-primary",
            "target_id" =>  "addUserModal",
            "text"      =>  "Kullanıcı Ekle",
            "icon" => "fas fa-plus"
        ])<br><br>
        <div class="table-responsive usersTable"></div> 
        <div class="messageAlert"></div> 
        @include('modal',[
            "id"=>"addUserModal",
            "title" => "Kullanıcı Ekleme",
            "url" => API('addUser'),
            "next" => "reload",
            "inputs" => [
                "Kullanıcı Adı" => "username:text:Kullanıcı Adı",
                "Şifre" => "password:password:Şifrenizi Giriniz",
            ],
            "selects" => [
                "Var:Var" => [
                    "True:True" => "type:hidden",
                ],
                "Yok:Yok" => [
                    "False:False" => "type:hidden",
                ],
            ],
            "submit_text" => "Ekle"
            ])<br><br>
    </div>
    <div id="varlik" class="tab-pane">
        <div class="row no-gutters">
            <div class="col" id="treeDiv">
                <div class="card" style="min-height: 400px;float:left;width: 100%;">
                    <div class="card-body" id="fileTreeWrapper" style="overflow-y: auto;">
                        <div id="fileTree"></div>
                    </div>
                </div>
            </div>
            <div class="col">
                <textarea style="width:100%;height: 95%; min-height: 400px;" id="textDiv"></textarea>
            </div>

        </div>
    </div>
</div>


<script>
    if(location.hash === ""){
        getHosts();
    }

    function getFiles(){
        let types = {
            "directory" : {
                "icon" : "fas fa-folder"
            },
            "file" : {
                "icon" : "fas fa-file"
            },
        };
        var form = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request("{{API('getFiles')}}", form, function(response) {
            message = JSON.parse(response)["message"]
            var json = JSON.parse(message.replace(/&quot;/g,'"'));
            $('#fileTree').jstree({ 'core' : {
                'data' : json 
            }, 
                plugins : ["types", "wholerow", "sort", "grid"],
                types : types,
            }).on('changed.jstree', function (event, data) {
                filepath = data["node"]["original"]["text"];
                filetype =data["node"]["original"]["type"];
                if(filetype == "file"){
                    showSwal('{{__("Yükleniyor...")}}','info');
                    var fileform = new FormData();
                    fileform.append("filepath",filepath)
                    request("{{API('getFileContent')}}", fileform, function(res) {
                        output = JSON.parse(res)["message"]
                        $('#textDiv').html(output);
                        Swal.close();
                    }, function(error) {});
                }
            });
            Swal.close();
        }, function(error) {
            status = JSON.parse(error)["status"]
            if(status == "202"){
                $('#varlik').html("<div class='alert alert-info  '><h5><i class='fas fa-info'></i> Bilgi !</h5>Henüz varlık bulunmamaktadır</div>");
            }
            Swal.close();
        });

    }

    function getUsers(){
        var form = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request("{{API('getUsers')}}", form, function(response) {

            $('.usersTable').html(response).find('table').DataTable({
                bFilter: true,
                "language" : {
                    url : "/turkce.json"
                }
            }); 
            $('td#password').css('-webkit-text-security', 'disc'); 
            Swal.close();
        }, function(error) {
            status = JSON.parse(error)["status"];
            if(status == 202){
                $('.messageAlert').html("<div class='alert alert-info  '><h5><i class='fas fa-info'></i> Bilgi !</h5>Kullanıcı bulunmamaktadır</div>");
            }else{
                $('.messageAlert').html("<div class='alert alert-danger '><h5><i class='fas fa-exclamation-triangle'></i> Hata !</h5>Hata Oluştu. Yetkili ile iletişime geçiniz</div>");
            }
            Swal.close();
        });
    }

    function getHosts(){
        var form = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request("{{API('getHosts')}}", form, function(response) {
            $('#hosts').html(response);
            Swal.close();
        }, function(error) {
            $('#hosts').html("Hata oluştu");
            Swal.close();
        });
    }

</script>
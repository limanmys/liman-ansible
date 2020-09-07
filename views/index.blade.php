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

</div>


<script>

    if(location.hash === ""){
        getHosts();
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
            $('#users').html("<div class='alert alert-danger '><h5><i class='fas fa-exclamation-triangle'></i> Hata !</h5>Hata Oluştu. Yetkili ile iletişime geçiniz</div>");
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
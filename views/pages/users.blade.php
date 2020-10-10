@include('modal-button',[
    "class"     =>  "btn btn-outline-primary mb-2",
    "target_id" =>  "addUserModal",
    "text"      =>  "Kullanıcı Ekle",
    "icon" => "fas fa-plus"
])

<div id="usersTable"></div>
<div class="messageAlert"></div>

@include('modal',[
    "id"=>"addUserModal",
    "title" => "Kullanıcı Ekleme",
    "url" => API('add_user'),
    "next" => "reload",
    "inputs" => [
        "Sudo:type" => ["Var"=>"Var","Yok"=>"Yok"],
        "Kullanıcı Adı" => "username:text:Kullanıcı Adı",
        "Şifre" => "password:password:Şifrenizi Giriniz",
    ],
    "submit_text" => "Ekle"
])
<script>
    function getUsers(){
        var form = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request("{{API('get_users')}}", form, function(response) {
            $('#usersTable').html(response).find('table').DataTable({
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
</script>
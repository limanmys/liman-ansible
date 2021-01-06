@include('modal-button',[
    "class"     =>  "btn btn-primary mb-2",
    "target_id" =>  "addGroupModal",
    "text"      =>  "Grup Ekle",
    "icon" => "fas fa-plus"
])

<div id="hostsDiv"></div>

@component('modal-component',[
    "id" => "hostContentModal",
    "title" => "Host İçeriği"
])
    @include('modal-button',[
        "class" => "btn btn-primary mb-2",
        "target_id" => "addClientIpModal",
        "text" => "Client Ekle",
        "icon" => "fas fa-plus mr-1"
    ])
    <div id="hostContentTable"></div>
@endcomponent

@component('modal-component',[
    "id" => "addClientIpModal",
    "title" => "Client Ip Ekleme",
    "footer" => [
        "text" => "Ekle",
        "class" => "btn-primary",
        "onclick" => "addClient()"
    ]
])
    @include('inputs', [
        "inputs" => [
            "Ip Adresi" => "ipaddress:text:Ip Adresi (Örn : 172.0.0.1)",
            "Ssh Kullanıcı" => "sshUserName:text:Bağlanılacak makinenin ssh kullanıcı adı",
            "Ssh Parola" => "sshUserPass:password:Bağlanılacak makinenin ssh kullanıcısının şifresi (isteğe  bağlı)",
        ]
    ])
@endcomponent

@include('modal',[
    "id"=>"addGroupModal",
    "title" => "Grup Ekleme",
    "url" => API('add_group'),
    "next" => "reload",
    "inputs" => [
        "Grup Adı" => "groupname:text:Grup Adı",
    ],
    "submit_text" => "Ekle"
])

@component('modal-component',[
    "id" => "addSshKeyComponent",
    "title" => "Ssh Key Ekle",
    "footer" => [
        "text" => "Ekle",
        "class" => "btn-success",
        "onclick" => "addSshKey()"
    ]
])
    @include('inputs', [
        "inputs" => [
            "Eklenecek makinenin local kullanıcısı" => "sshUserName:text:pardus",
            "Eklenecek makinenin local kullanıcısının şifresi" => "sshUserPass:password:1",
            "ipaddress:ipaddress" => "ipaddress:hidden",
        ]
    ])
@endcomponent

@component('modal-component',[
    "id" => "removeSshKeyComponent",
    "title" => "Ssh Key Kaldır",
    "footer" => [
        "text" => "Kaldır",
        "class" => "btn-danger",
        "onclick" => "removeSshKey()"
    ]
])
    @include('inputs', [
        "inputs" => [
             "Eklenecek makinenin local kullanıcısı" => "sshUserName:text:pardus",
            "Eklenecek makinenin local kullanıcısının şifresi" => "sshUserPass:password:1",
            "ipaddress:ipaddress" => "ipaddress:hidden",
        ]
    ])
@endcomponent


<script>
    let HOSTNAME =  ""

    function deleteGroup(line){
        Swal.fire({
            title: "{{ __('Onay') }}",
            text: "{{ __('Silmek istediğinize emin misiniz?') }}",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085D6',
            cancelButtonColor: '#d33',
            cancelButtonText: "{{ __('İptal') }}",
            confirmButtonText: "{{ __('Sil') }}"
        }).then((result) => {
            if (result.value) {
                showSwal('{{__("Siliniyor..")}}','info');
                let groupName = line.querySelector("#name").innerHTML;
                let formData = new FormData();
                formData.append("groupName",groupName)
                request(API("delete_group") ,formData,function(response){
                    showSwal('{{__("Silindi")}}', 'success',2000);
                    getHosts()
                }, function(response){
                    let error = JSON.parse(response);
                    if(error.message=="WARNING"){
                        Swal.fire({
                            position: 'center',
                            type: 'warning',
                            title: 'UYARI',
                            text: 'Bu grup dolu olduğundan silinemez...',
                        });
                    }else{
                        showSwal(error.message, 'error');
                    }
                });
            }
        });
    }

    function addSshKey(){
        showSwal('{{__("Ekleniyor..")}}','info');
        let ipAddress = $('#addSshKeyComponent').find('input[name=ipaddress]').val();
        let sshUserName = $('#addSshKeyComponent').find('input[name=sshUserName]').val();
        let sshUserPass = $('#addSshKeyComponent').find('input[name=sshUserPass]').val();
        let formData = new FormData();
        formData.append("sshUserName",sshUserName)
        formData.append("sshUserPass",sshUserPass)
        formData.append("ipAddress",ipAddress)
        request(API("add_ssh_key") ,formData,function(response){
            $('#addSshKeyComponent').modal("hide");
            showSwal('{{__("Eklendi")}}', 'success',2000);
        }, function(response){
            let error = JSON.parse(response);
            showSwal(error.message, 'error');
        });
    }
    
    function openAddSshKeyComponent(line){
        let ipAddress = line.querySelector("#ip").innerHTML;
        $('#addSshKeyComponent').find('input[name=ipaddress]').val(ipAddress);
        $('#addSshKeyComponent').modal("show"); 
    }

    function removeSshKey(){
        showSwal('{{__("Kaldırılıyor..")}}','info');
        let ipAddress = $('#removeSshKeyComponent').find('input[name=ipaddress]').val();
        let sshUserName = $('#removeSshKeyComponent').find('input[name=sshUserName]').val();
        let sshUserPass = $('#removeSshKeyComponent').find('input[name=sshUserPass]').val();
        let formData = new FormData();
        formData.append("sshUserName",sshUserName)
        formData.append("sshUserPass",sshUserPass)
        formData.append("ipAddress",ipAddress)
        request(API("remove_ssh_key") ,formData,function(response){
            $('#removeSshKeyComponent').modal("hide");
            showSwal('{{__("Kaldırıldı")}}', 'success',2000);
        }, function(response){
            let error = JSON.parse(response);
            showSwal(error.message, 'error');
        });
    }

    function openRemoveSshKeyComponent(line){
        let ipAddress = line.querySelector("#ip").innerHTML;
        $('#removeSshKeyComponent').find('input[name=ipaddress]').val(ipAddress);
        $('#removeSshKeyComponent').modal("show"); 
    }

    function addClient(){
        showSwal('{{__("Ekleniyor..")}}','info');
        let formData = new FormData();
        let ip = $('#addClientIpModal').find('input[name=ipaddress]').val();
        let user = $('#addClientIpModal').find('input[name=sshUserName]').val();
        let pass = $('#addClientIpModal').find('input[name=sshUserPass]').val();
        formData.append("hostsname",HOSTNAME)
        formData.append("ipaddress",ip)
        formData.append("sshUserName",user)
        formData.append("sshUserPass",pass)
        request(API("add_host") ,formData,function(response){
            showSwal('{{__("Eklendi")}}', 'success',2000);
            reloadModalTable();
        }, function(response){
            let error = JSON.parse(response);
            showSwal(error.message, 'error');
        }); 
    }

    function deleteClientIpJS(line){
        Swal.fire({
            title: "{{ __('Onay') }}",
            text: "{{ __('Silmek istediğinize emin misiniz?') }}",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085D6',
            cancelButtonColor: '#d33',
            cancelButtonText: "{{ __('İptal') }}",
            confirmButtonText: "{{ __('Sil') }}"
        }).then((result) => {
            if (result.value) {
                showSwal('{{__("Siliniyor..")}}','info');
                let formData = new FormData();
                let ip = line.querySelector("#ip").innerHTML;
                let ansibleSshUser = line.querySelector("#ssh_user").innerHTML;
                formData.append("deletehostsname",HOSTNAME);
                formData.append("ansibleSshUser",ansibleSshUser);
                formData.append("ipaddress",ip);
                request(API("delete_ip") ,formData,function(response){
                    showSwal('{{__("Silindi")}}', 'success',2000);
                    reloadModalTable();
                }, function(response){
                    let error = JSON.parse(response);
                    showSwal(error.message, 'error');
                });
            }
        });
    }

    function getHosts(){
        let form = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request(API('get_hosts'), form, function(response) {
            $('#hostsDiv').html(response).find('table').DataTable(dataTablePresets('normal'));
            Swal.close();
        }, function(error) {
            errorMessage = JSON.parse(error)["message"]
            showSwal(errorMessage,'error');
        });
    }

    function getHostsContent(line){
        let hostName = line.querySelector("#name").innerHTML;
        HOSTNAME = hostName;
        let form = new FormData();
        form.append("hostName",hostName);
        showSwal('{{__("Yükleniyor...")}}','info');
        request(API('get_host_content'), form, function(response) {
            $('#hostContentTable').html(response).find('table').DataTable(dataTablePresets('normal'));
            $('#hostContentModal').modal("show"); 
            Swal.close();
        }, function(error) {
            errorMessage = JSON.parse(error)["message"]
            showSwal(errorMessage,'error');
        });
    }

    function reloadModalTable(){
        let form = new FormData();
        form.append("hostName",HOSTNAME);
        showSwal('{{__("Yükleniyor...")}}','info');
        request(API('get_host_content'), form, function(response) {
            $("#addClientIpModal").modal("hide");
            $('#hostContentTable').html(response).find('table').DataTable(dataTablePresets('normal'));
            Swal.close();
        }, function(error) {
            errorMessage = JSON.parse(error)["message"]
            showSwal(errorMessage,'error');
        });
    }
    
</script>
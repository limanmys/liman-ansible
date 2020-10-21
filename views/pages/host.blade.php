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
        "Ip Adresi" => "ipaddress:text:Ip Adresi Giriniz",
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
            "Kullanıcılar:user" => \App\Controllers\HostsController::getUserSelect(),
            "ipaddress:ipaddress" => "ipaddress:hidden",
        ]
    ])
@endcomponent


<script>
    let HOSTNAME =  ""

    function addSshKey(){
        showSwal('{{__("Ekleniyor..")}}','info');
        let ipAddress = $('#addSshKeyComponent').find('input[name=ipaddress]').val();
        let username = $('#addSshKeyComponent').find('select[name=user]').val();
        let formData = new FormData();
        formData.append("username",username)
        formData.append("ipAddress",ipAddress)
        request(API("add_ssh_key") ,formData,function(response){
            showSwal('{{__("Eklendi")}}', 'success',2000);
        }, function(response){
            let error = JSON.parse(response);
            showSwal(error.message, 'error');
        });
    }

    function openAddSshKeyComponent(line){
        let ipAddress = line.querySelector("#ip").innerHTML;
        console.log(ipAddress)
        $('#addSshKeyComponent').find('input[name=ipaddress]').val(ipAddress);
        $('#addSshKeyComponent').modal("show"); 
    }

    function addClient(){
        showSwal('{{__("Ekleniyor..")}}','info');
        let formData = new FormData();
        let ip = $('#addClientIpModal').find('input[name=ipaddress]').val();
        formData.append("hostsname",HOSTNAME)
        formData.append("ipaddress",ip)
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
                formData.append("deletehostsname",HOSTNAME);
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
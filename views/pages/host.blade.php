<div id="hostsDiv"></div>





@component('modal-component',[
    "id" => "hostContentModal",
    "title" => "Host İçeriği"
])
    <button class="btn btn-primary mb-2" onclick="addClientIpJS()">
        <i class="fas fa-plus"></i> {{ __('Client Ekle') }}
    </button>
    <div id="hostContentTable"></div>
@endcomponent

@include('modal',[
    "id"=>"addClientIpModal",
    "title" => "Client Ip Ekleme",
    "url" => API('add_host'),
    "next" => "reloadModalTable",
    "inputs" => [
        "hostsname:hostname" => "hostsname:hidden",
        "Ip Adresi" => "ipaddress:text:Ip Adresi (Örn : 172.0.0.1)",
    ],
    "submit_text" => "Ekle"
])

<script>
    let HOSTNAME =  ""

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

    function addClientIpJS(line){
        $('#addClientIpModal').find('input[name=hostsname]').val(HOSTNAME);
        $("#addClientIpModal").modal("show");
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
    console.log(HOSTNAME)

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
<div id="logTable2"></div>

@component('modal-component',[
    "id" => "showLogContentComponent",
    "title" => "Dosya İçeriği"
])
@endcomponent
<script>
    function getLogs(){
        let form = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request(API('get_logs'), form, function(response) {
            $('#logTable2').html(response).find('table').DataTable(dataTablePresets('normal'));
            Swal.close();
        }, function(error) {
            error = JSON.parse(error)["message"]
            showSwal(error,'error');
        });
    }

    function showLogContent(line){
        let fileName = line.querySelector("#name").innerHTML;
        let formData = new FormData();
        //console.log(line.querySelector("#name").innerHTML);
        formData.append("fileName",fileName);
        request(API("get_content_log"), formData, function(response){
            let filecontent = JSON.parse(response).message
            $("#showLogContentComponent").find('.modal-body').html("<pre style='background-color: #EBECE4; '>"+filecontent+"</pre>");
            $('#showLogContentComponent').modal("show"); 
            
            Swal.close();
        },function(response){
            showSwal('{{__("Log göstermede hata oluştu")}}','error');
        });
    }

    function deleteLog(line){
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
                let fileName = line.querySelector('#name').innerHTML;
                let formData = new FormData();
                formData.append("fileName",fileName);
                request(API("delete_log") ,formData,function(response){
                    showSwal('{{__("Silindi")}}', 'success',2000);
                    getLogs();
                }, function(response){
                    let error = JSON.parse(response);
                    showSwal(error.message, 'error');
                });
            }
        });
    }
</script>
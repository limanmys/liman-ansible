 <button  class="btn btn-primary mb-2" onclick="openPlaybookComponent()">
    <i class="fas fa-plus"></i> {{ __('Dosya Oluştur') }}
</button>

@component('modal-component',[
    "id" => "createPlaybookComponent",
    "title" => "Dosya Oluştur",
    "footer" => [
        "text" => "Oluştur",
        "class" => "btn-primary",
        "onclick" => "createPlaybookFile()"
    ]
])
    @include('inputs', [
        "inputs" => [
            "Dosya Adı" => "filename:text:Dosya adını giriniz",
        ]
    ])
@endcomponent

<div id="playbookTable"></div>

@component('modal-component',[
    "id" => "editPlaybookComponent",
    "title" => "Dosya İçeriği",
    "footer" => [
        "text" => "Güncelle",
        "class" => "btn-primary",
        "onclick" => "editPlaybook()"
    ]
])
    @include('inputs', [
        "inputs" => [
            "Dosya içeriği" => "contentfile:textarea",
            "filename:filename" => "filename:hidden",
        ]
    ])
@endcomponent

@component('modal-component',[
    "id" => "showPlaybookContentComponent",
    "title" => "Dosya İçeriği"
])
@endcomponent

@component('modal-component',[
    "id" => "runPlaybookComponent",
    "title" => "Playbook Çalıştır",
    "footer" => [
        "text" => "Çalıştır",
        "class" => "btn-success",
        "onclick" => "runPlaybook()"
    ]
])
    @include('inputs', [
        "inputs" => [
            "Grup:group" => \App\Controllers\PlaybookController::getHostsSelect(),
            "filename:filename" => "filename:hidden",
        ]
    ])
@endcomponent

<script>

    function saveLogPlaybook(){
        Swal.fire({
            title: "Log Kaydet",
            inputAttributes: {
                placeholder: 'Dosya Adı'
            },
            input: 'text',
            showCancelButton: true,
            confirmButtonText: 'Kaydet',
            }).then((result) => {
                if (result.value) {
                    let formData = new FormData();
                    let logContent = $("#playbookTaskModal").find('#outputArea').text();
                    formData.append("logFileName", result.value);
                    formData.append("logFileContent", logContent);
                    request(API("playbook_save_output") ,formData,function(response){
                        $('#playbookTaskModal').modal('hide');
                        showSwal('{{__("Kaydedildi")}}', 'success',2000);
                    }, function(response){
                        let error = JSON.parse(response);
                        showSwal(error.message, 'error');
                    }); 
                }
        });
    }
    function runPlaybook(){
        showSwal('{{__("Yükleniyor...")}}', 'info');
        let fileName = $("#runPlaybookComponent").find('input[name="filename"]').val(); 
        let group = $("#runPlaybookComponent").find('select[name="group"]').val(); 
        let formData = new FormData();
        formData.append("filename", fileName);
        formData.append("group", group);
        request(API("run_playbook"), formData, function(response) {
            $('#runPlaybookComponent').modal('hide');
            $('#playbookTaskModal').find('.modal-body').html(JSON.parse(response).message);
            $('#playbookTaskModal').modal("show"); 
            Swal.close();
        }, function(response) {
            let error = JSON.parse(response).message
            showSwal(error, 'error');
        });
    }

    function openRunPlaybookComponent(line){ 
        let fileName = line.querySelector("#name").innerHTML;
        $("#runPlaybookComponent").find('input[name="filename"]').val(fileName); 
        $('#runPlaybookComponent').modal('show');
    }

    function openPlaybookComponent(){
        $('#createPlaybookComponent').find('#fileTextarea').remove();
        let textareaFormElement = '<div class="form-group" id="fileTextarea"><div class="form-group"><label>Dosya İçeriği</label><textarea class="form-control" placeholder="Dosya İçeriği" id="filecontent" rows="5"></textarea></div>'
        $('#createPlaybookComponent').find('.modal-body').append(textareaFormElement);
        $('#createPlaybookComponent').modal('show');
    }

    function createPlaybookFile(){
        showSwal('{{__("Oluşturuluyor..")}}','info');
        let fileName = $("#createPlaybookComponent").find('input[name="filename"]').val(); 
        let fileContent = $("#filecontent").val();
        let formData = new FormData();
        formData.append("fileName",fileName);
        formData.append("fileContent",fileContent);
        request(API("create_playbook") ,formData,function(response){
            showSwal('{{__("Oluşturuldu")}}', 'success',2000);
            reload()
        }, function(response){
            let error = JSON.parse(response);
            showSwal(error.message, 'error');
        });
    }

    function getPlaybooks(){
        let form = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request(API('get_playbooks'), form, function(response) {
            $('#playbookTable').html(response).find('table').DataTable(dataTablePresets('normal'));
            Swal.close();
        }, function(error) {
            error = JSON.parse(error)["message"]
            showSwal(error,'error');
        });
    }

    function showPlaybookContent(line){
        let fileName = line.querySelector("#name").innerHTML;
        let formData = new FormData()
        formData.append("fileName",fileName);
        request(API("get_content_playbook"), formData, function(response){
            let filecontent = JSON.parse(response).message
            $("#showPlaybookContentComponent").find('.modal-body').html("<pre style='background-color: #EBECE4; '>"+filecontent+"</pre>");
            $('#showPlaybookContentComponent').modal("show"); 
            Swal.close();
        },function(response){
            showSwal('{{__("Playbook göstermede hata oluştu")}}','error');
        });
    }

    function openPlaybookEditComponent(line){
        showSwal('{{__("Yükleniyor..")}}','info');
        let fileName = line.querySelector("#name").innerHTML;
        $("#editPlaybookComponent").find('input[name="filename"]').val(fileName); 
        let formData = new FormData()
        formData.append("fileName",fileName);
        request(API("get_content_playbook"), formData, function(response){
            let filecontent = JSON.parse(response).message
            $("#editPlaybookComponent").find('textarea[name="contentfile"]').attr('rows',10);
            $("#editPlaybookComponent").find('textarea[name="contentfile"]').val(filecontent); 
            $('#editPlaybookComponent').modal("show"); 
            Swal.close();
        },function(response){
            showSwal('{{__("Güncellemede hata oluştu")}}','error');
        });
    }

    function editPlaybook(){
        showSwal('{{__("Güncelleniyor..")}}','info');
        let fileName = $("#editPlaybookComponent").find('input[name="filename"]').val(); 
        let contentFile = $("#editPlaybookComponent").find('textarea[name="contentfile"]').val(); 
        let formData = new FormData()
        formData.append("fileName",fileName);
        formData.append("contentFile",contentFile);
        request(API("edit_playbook"), formData, function(response){
            $('#editPlaybookComponent').modal("hide"); 
            showSwal('{{__("Güncellendi")}}','success',2000);
        },function(response){
            let error = JSON.parse(response).message
            showSwal(error,'error');
        });
    }

    function deletePlaybook(line){
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
                request(API("delete_playbook") ,formData,function(response){
                    showSwal('{{__("Silindi")}}', 'success',2000);
                    reload();
                }, function(response){
                    let error = JSON.parse(response);
                    showSwal(error.message, 'error');
                });
            }
        });
    }
</script>
<button  class="btn btn-primary mb-2" onclick="openPlaybookComponent()">
    <i class="fas fa-plus"></i> {{ __('Playbook Oluştur') }}
</button>
    <br><br>
    <h4>Playbook'lar</h4>
    <hr>
    <div id="playbookTable"></div>
    <!--
    <div class="col-sm-6">
        <div class="col" id="outputTextArea">
            <textarea style="width:100%;height:100%;min-height:185px;" id="outputText"></textarea>
            <button  class="btn btn-primary mb-2 float-right" id="fileEditButton" onclick="saveLogOutput()">
                <i class="fas fa-edit" ></i> {{ __('Kaydet') }}
            </button>
        </div>
        <h4>Log'lar</h4>
        <hr>
        <div id="playbookLogTable" style="width:100%;"></div
    </div>
    -->

@component('modal-component',[
    "id" => "createPlaybookComponent",
    "title" => "Playbook Oluştur",
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

@component('modal-component',[
    "id" => "showLogContentComponent",
    "title" => "Dosya İçeriği"
    ])
@endcomponent

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
            "Sudo Şifresi" => "passText:password:Sudo Şifresi giriniz",
        ]
    ])
@endcomponent

<script>   

    function runPlaybook(){
        showSwal('{{__("Yükleniyor...")}}', 'info');
        let fileName = $("#runPlaybookComponent").find('input[name="filename"]').val(); 
        let group = $("#runPlaybookComponent").find('select[name="group"]').val(); 
        let passText = $("#runPlaybookComponent").find('input[name="passText"]').val(); 
        let formData = new FormData();
        formData.append("filename", fileName);
        formData.append("passText", passText);
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

        /*$('#playbookTaskModal').on('hidden.bs.modal',  () => {
            showSwal('{{__("Yükleniyor...")}}','info');
            let data = new FormData();
            request(API("get_output"), data, function(response) {
                $("#outputText").val(response);   
                Swal.close();  
            }, function(response) {
                $("#outputText").val('');
                let error = JSON.parse(response);
                showSwal(error.message, 'error', 3000);
            }); 
        })*/
    }

    function getPlaybooks(){
        //$('#outputText').val('');
        let form = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request(API('get_playbooks'), form, function(response) {
            $('#playbookTable').html(response).find('table').DataTable(dataTablePresets('normal'));
            Swal.close();
        }, function(error) {
            error = JSON.parse(error)["message"]
            showSwal(error,'error');
        });
        /*
        let data = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request(API('get_log'), data, function(response) {
            $('#playbookLogTable').html(response).find('table').DataTable(dataTableCustomTablePreset());
            Swal.close();
        }, function(error) {
            error = JSON.parse(error)["message"]
            showSwal(error,'error');
        });*/
    }

    function dataTableCustomTablePreset(){
        return Object.assign(
            dataTablePresets('normal'),
            {
                "paging": true,
                "info": true,
                "searching": true,
                "lengthMenu": [ 5, 10, 25, 50 ]
            }
        );        
    }

    function showLogContent(line){
        let fileName = line.querySelector("#name").innerHTML + "-.-" + line.querySelector("#user").innerHTML;
        let formData = new FormData();
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
                if (result.value.indexOf(' ') < 0) {
                    let formData = new FormData();
                    let logContent = $("#playbookTaskModal").find('#outputArea').text();
                    formData.append("logFileName", result.value);
                    formData.append("logFileContent", logContent);
                    request(API("playbook_save_task") ,formData,function(response){
                        showSwal('{{__("Kaydedildi")}}', 'success',2000);
                        setTimeout(function(){
                            $('#playbookTaskModal').modal('hide');
                        }, 1500);
                    }, function(response){
                        let error = JSON.parse(response);
                        showSwal(error.message, 'error');
                    }); 
                }
                else {
                    showSwal('{{__("Dosya adı boşluk içermemelidir!")}}', 'error', 2000);
                }
        });
        $('#playbookTaskModal').on('hidden.bs.modal',  () => {
            getPlaybooks();
        })
    }
    function saveLogOutput(){
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
                formData.append("textArea",$("#outputText").val());
                formData.append("logFileName", result.value);
                request(API("playbook_save_output") ,formData,function(response){
                    showSwal('{{__("Kaydedildi")}}', 'success',2000);
                    getPlaybooks();
                }, function(response){
                    let error = JSON.parse(response);
                    showSwal(error.message, 'error', 3000);
                });
            }
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
                    getPlaybooks();
                }, function(response){
                    let error = JSON.parse(response);
                    showSwal(error.message, 'error');
                });
            }
        });
    }
    
    function deletePlaybookLog(line){
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
                let fileName = line.querySelector("#name").innerHTML + "-.-" + line.querySelector("#user").innerHTML;
                let formData = new FormData();
                formData.append("fileName",fileName);
                request(API("delete_playbook_log") ,formData,function(response){
                    showSwal('{{__("Silindi")}}', 'success',2000);
                    getPlaybooks();
                }, function(response){
                    let error = JSON.parse(response);
                    showSwal(error.message, 'error');
                });
            }
        });
    }
</script>
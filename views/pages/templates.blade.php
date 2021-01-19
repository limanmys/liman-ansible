
@include('modal-button',[
    "class" => "btn-primary mb-2",
    "target_id" => "createTemplate",
    "text" => "Template Oluştur",
    "icon" => "fas fa-plus mr-1"
])

<div id="templateTable"></div>

@component('modal-component',[
    "id" => "createTemplate",
    "title" => "Template Oluştur",
    "footer" => [
        "text" => "Oluştur",
        "class" => "btn-primary",
        "onclick" => "createTemplate()"
    ]
])
    @include('inputs', [
        "inputs" => [
            "Dosya Adı" => "filename:text:Dosya adını giriniz",
        ]
    ])
    <div class="form-group">
        <label for="contentfile">Dosya İçeriği</label>
        <textarea class="form-control" name="contentfile" rows="10"></textarea>
    </div>
@endcomponent

@component('modal-component',[
    "id" => "showTemplateContentComponent",
    "title" => "Dosya İçeriği"
])
    <pre id="contentTemplate" style="background-color: #EBECE4;" ></pre>
@endcomponent

@component('modal-component',[
    "id" => "editTemplateComponent",
    "title" => "Dosya İçeriği",
    "footer" => [
        "text" => "Güncelle",
        "class" => "btn-primary",
        "onclick" => "editTemplate()"
    ]
])
    @include('inputs', [
        "inputs" => [
            "filename:filename" => "filename:hidden",
        ]
    ])
    <div class="form-group">
        <label for="contentfile">Dosya İçeriği</label>
        <textarea class="form-control" name="contentfile" rows="10"></textarea>
    </div>
@endcomponent

<script>
    function getTemplates(){
        let form = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request(API('get_templates'), form, function(response) {
            $('#templateTable').html(response).find('table').DataTable(dataTablePresets('normal'));
            Swal.close();
        }, function(error) {
            error = JSON.parse(error)["message"]
            showSwal(error,'error');
        });
    }

    function createTemplate(){
        showSwal('{{__("Oluşturuluyor..")}}','info');
        let fileName = $("#createTemplate").find('input[name="filename"]').val(); 
        let fileContent = $("#createTemplate").find('textarea[name="contentfile"]').val(); 
        let formData = new FormData();
        formData.append("fileName",fileName);
        formData.append("fileContent",fileContent);
        request(API("create_template") ,formData,function(response){
            showSwal('{{__("Oluşturuldu")}}', 'success',2000);
            $("#createTemplate").modal("hide");
            $("#createTemplate").find('input[name="filename"]').val(""); 
            $("#createTemplate").find('textarea[name="contentfile"]').val(""); 
            getTemplates()
        }, function(response){
            let error = JSON.parse(response);
            showSwal(error.message, 'error');
        });
    }

    function deleteTemplate(line){
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
                request(API("delete_template") ,formData,function(response){
                    showSwal('{{__("Silindi")}}', 'success',2000);
                    getTemplates()
                }, function(response){
                    let error = JSON.parse(response);
                    showSwal(error.message, 'error');
                });
            }
        });
    }

    function showTemplateContent(line){
        let fileName = line.querySelector("#name").innerHTML;
        let formData = new FormData()
        formData.append("fileName",fileName);
        request(API("get_content_template"), formData, function(response){
            let filecontent = JSON.parse(response).message
            $("#showTemplateContentComponent").find('#contentTemplate').text(filecontent);
            $('#showTemplateContentComponent').modal("show"); 
            Swal.close();
        },function(response){
            showSwal('{{__("Template göstermede hata oluştu")}}','error');
        });
    }

    function openTemplateEditComponent(line){
        showSwal('{{__("Yükleniyor..")}}','info');
        let fileName = line.querySelector("#name").innerHTML;
        $("#editTemplateComponent").find('input[name="filename"]').val(fileName); 
        let formData = new FormData()
        formData.append("fileName",fileName);
        request(API("get_content_template"), formData, function(response){
            let filecontent = JSON.parse(response).message
            $("#editTemplateComponent").find('textarea[name="contentfile"]').val(filecontent); 
            $('#editTemplateComponent').modal("show"); 
            Swal.close();
        },function(response){
            showSwal('{{__("Güncellemede hata oluştu")}}','error');
        });
    }

    function editTemplate(){
        showSwal('{{__("Güncelleniyor..")}}','info');
        let fileName = $("#editTemplateComponent").find('input[name="filename"]').val(); 
        let contentFile = $("#editTemplateComponent").find('textarea[name="contentfile"]').val(); 
        let formData = new FormData()
        formData.append("fileName",fileName);
        formData.append("contentFile",contentFile);
        request(API("edit_template"), formData, function(response){
            $('#editTemplateComponent').modal("hide"); 
            showSwal('{{__("Güncellendi")}}','success',2000);
        },function(response){
            let error = JSON.parse(response).message
            showSwal(error,'error');
        });
    }
</script>
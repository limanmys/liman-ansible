@include('modal-button',[
    "class" => "btn btn-primary mb-3",
    "target_id" => "fileUpload",
    "text" => "Dosya Ekle",
    "icon" => "fas fa-plus mr-1"
])

@component('modal-component',[
    "id" => "fileUpload",
    "title" => "İçeri Aktar",
])
    @include('inputs', [
        "inputs" => [
            "Klasör Adı" => "dirname:text",
        ]
    ])
    @include('file-input', [
        'title' => 'Dosya Yükle',
        'id' => 'test',
        'name' => 'file_upload',
        'callback' => 'onSuccess'
    ])
@endcomponent
<div id="filesDiv"></div>


<script>
    $('#test-upload-file').change(function () {
        let fileName = $("#fileUpload [name='file_upload']").val()
        let allowExtension = ["gz", "zip", "tar"];
        extension = fileName.split('.').pop();
        if(allowExtension.indexOf(extension) == "-1"){
            $("#fileUpload [name='file_upload']").val("")
            Swal.fire({
                type: 'error',
                title: 'Yanlış Format...',
                text: 'Dosya formatı zip, tar.gz veya tar uzantılı olabilir.',
            })
        }
    }); 

    function getFileContent(data2){
        showSwal('{{__("Yükleniyor...")}}','info');
        var data = new FormData();
        var filePath = data2.node.id;
        data.append("filePath",filePath);
        request("{{API('get_file_content')}}", data, function(response) {
            $("#textDiv").val(response);
            Swal.close();
        }, function(error) {
            error = JSON.parse(error)["message"];
            showSwal(error,'error');
        });
    }

    function editFile(){
        Swal.fire({
            title: "{{ __('Onay') }}",
            text: "{{ __('Güncellemek istediğinize emin misiniz?') }}",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085D6',
            cancelButtonColor: '#d33',
            cancelButtonText: "{{ __('İptal') }}",
            confirmButtonText: "{{ __('Güncelle') }}"
            
        }).then((result) => {
            if (result.value) {
                showSwal('{{__("Güncelleniyor..")}}','info');
                let data = new FormData();
                let text = $('#textDiv').val();
                let filePath = $("#fileTree").jstree("get_selected",true)[0]["original"]["id"];
                data.append("filePath",filePath);
                data.append("text",text);
                request(API('edit_file'), data, function(res) {
                    showSwal('{{__("Güncellendi")}}','success',2000);
                }, function(res) {
                    let error = JSON.parse(res);
                    showSwal(error.message,'error');
                });
            }
        });
    }

    function onSuccess(upload){
        let data = new FormData();
        let dirName = $("#fileUpload [name='dirname']").val();
        showSwal('{{__("Ekleniyor...")}}','info');
        data.append('name', upload.info.name);
        data.append('path', upload.info.file_path);
        data.append('dirName',dirName);
        request(API("upload_file"), data, function(response){
            try {
                showSwal("Eklendi",'success',2000);
                reload();
            } catch(e) {
                showSwal('{{__("Dosya karşı sunucuya gönderilirken hata oluştu!")}}','error',2000);
            }
        }, function(response){
            let error = JSON.parse(response);
            showSwal(error.message,'error');
        });
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
        request("{{API('get_files')}}", form, function(response) {
            $("#filesDiv").html(response);
            Swal.close();
        }, function(error) {
            error = JSON.parse(error)["message"]
            showSwal(error,'error');
        });
    }
</script>
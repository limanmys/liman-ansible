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
        'name' => 'file_upload',
        'callback' => 'onSuccess'
    ])
@endcomponent

<div class="row no-gutters">
    <div class="col-md-6" id="treeDiv">
        <div class="card" style="min-height: 400px;float:left;width: 100%;">
            <div class="card-body" id="fileTreeWrapper" style="overflow-y: auto;">
                <div id="fileTree"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6" id="fileTextDiv">
        <textarea style="width:100%;height: 80%; min-height: 400px;" id="textDiv"></textarea>
        <button  class="btn btn-primary mb-2 float-right" id="fileEditButton" onclick="editFile()" >
            <i class="fas fa-edit"></i> {{ __('Dosya Güncelle') }}
        </button>
    </div>
</div>

<script>

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
                let text = $('#textDiv').val()
                let filePath = $("#fileTree").jstree("get_selected",true)[0]["original"]["text"];
                data.append("filePath",filePath)
                data.append("text",text)
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

    $('#fileTree').on('changed.jstree', function (event, data) {
        $('#textDiv').val("")
        filepath = data["node"]["original"]["text"];
        filetype =data["node"]["original"]["type"];
        if(filetype == "file"){
            $("#fileEditButton").removeAttr('disabled');
            showSwal('{{__("Yükleniyor...")}}','info');
            var fileform = new FormData();
            fileform.append("filepath",filepath)
            request("{{API('get_file_content')}}", fileform, function(res) {
                output = JSON.parse(res)["message"]
                $('#textDiv').val(output);
                Swal.close();
            }, function(error) {});
        }else{
            $("#fileEditButton").attr('disabled','disabled');
        }
    });

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
            message = JSON.parse(response)["message"]
            var json = JSON.parse(message.replace(/&quot;/g,'"'));
            $('#fileTree').jstree({ 'core' : {
                'data' : json 
            }, 
                plugins : ["types", "wholerow", "sort", "grid"],
                types : types,
            })
            Swal.close();
        }, function(error) {
            status = JSON.parse(error)["status"]
            if(status == "202"){
                $('#varlik').html("<div class='alert alert-info  '><h5><i class='fas fa-info'></i> Bilgi !</h5>Henüz varlık bulunmamaktadır</div>");
            }
            Swal.close();
        });
    }
</script>
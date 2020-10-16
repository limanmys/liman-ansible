
@if($data != "")
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
@else
    <div class='alert alert-info w-100' role='alert'><h5><i class='fas fa-info md-2'></i> Bilgi !</h5>Henüz varlık bulunmamaktadır</div>
@endif

<script>
    let data = '{{$data}}'
    if(data != ""){
        data = JSON.parse(data.replace(/&quot;/g,'"'))
        console.log(data)
        let types = {
            "directory" : {
                "icon" : "fas fa-folder"
            },
            "file" : {
                "icon" : "fas fa-file"
            },
        };
        $('#fileTree').jstree({ 'core' : {
            'data' : data
        }, 
            plugins : ["types", "wholerow", "sort", "grid"],
            types : types,
        })
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
</script>
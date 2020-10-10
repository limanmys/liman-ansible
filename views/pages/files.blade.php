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
    </div>
</div>

<script>

$('#fileTree').on('changed.jstree', function (event, data) {
    filepath = data["node"]["original"]["text"];
    filetype =data["node"]["original"]["type"];
    if(filetype == "file"){
        showSwal('{{__("Yükleniyor...")}}','info');
        var fileform = new FormData();
        fileform.append("filepath",filepath)
        request("{{API('get_file_content')}}", fileform, function(res) {
            output = JSON.parse(res)["message"]
            $('#textDiv').html(output);
            Swal.close();
        }, function(error) {});
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

@if($data != "")
    <div class="row no-gutters">
        <div class="col" id="treeDiv">
            <div class="card" style="min-height: 400px;float:left;width: 100%;">
                <div class="card-body" id="fileTreeWrapper" style="overflow-y: auto;">
                    <div id="fileTree"></div>
                </div>
            </div>
        </div>
        <div class="col" id="fileTextDiv">
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
    data = '{{$data}}'

    var sizes = localStorage.getItem('split-sizes');
    if (sizes) {
        sizes = JSON.parse(sizes)
    } else {
        sizes = [25, 75] // default sizes
    }

    var splitobj = Split(["#treeDiv","#fileTextDiv"], {
        elementStyle: function (dimension, size, gutterSize) {
            $(window).trigger('resize');
            return {
                'width': 'calc(' + size + '% - ' + gutterSize + 'px)',
                'flex-basis': 'initial'
            }
        },
        gutterStyle: function (dimension, gutterSize) {
            let tableHeight = $('#fileTextDiv').outerHeight();
            let treeHeight = $('#treeDiv').outerHeight();
            return {
                'flex-basis':  gutterSize + 'px',
                'height': Math.min(tableHeight, treeHeight) + 'px',
                'z-index': 10
            }
        },
        onDragEnd: function(sizes) {
            localStorage.setItem('split-sizes', JSON.stringify(sizes))
        },
        sizes: sizes,
        minSize: 200,
        gutterSize: 6,
        cursor: 'col-resize'
    });

    if(data != ""){
        data = JSON.parse(data.replace(/&quot;/g,'"'))
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
        getFileContent(data);
    });

</script>
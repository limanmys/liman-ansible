<div class="row">
    <div>
        <button style="width:100%;height:38px;" class="btn btn-primary mb-2" onclick="runPlaybook2()" type="button">
            <i class="far fa-play-circle mr-2"></i> {{ __('Çalıştır') }}
        </button> 
    </div>
    <div class="col-sm-2">
        <select style="width:100%;" id="dropdown1" class="select2 select2-container select2-container--bootstrap4 select2-container--below select2-container--focus"></select>
    </div>
    <div class="col-sm-2">
        <input style="width:100%;height:38px;" type="password" name="sudoPassword" id="sudopass_field" class="form-control"
        placeholder="Sudo şifresini giriniz">
    </div>
  </div>

<div class="container-fluid mt-3">
    <div class="row">
      <div class="col-sm-6">
          <div class="col" id="fileTextDiv2">
            <textarea style="width:100%;height: 100%;min-height:550px;" id="textDiv2"></textarea>
            <button  class="btn btn-primary mb-2 float-right" id="fileEditButton" onclick="saveLogPlaybook2()">
                <i class="fas fa-edit" ></i> {{ __('Kaydet') }}
            </button>
        </div>
      </div>
      <div class="col-sm-6">
        <div id="logTable1"></div>
            @component('modal-component',[
            "id" => "showLogContentComponent2",
            "title" => "Dosya İçeriği"
            ])
            @endcomponent
      </div>
    </div>
  </div>

  @component('modal-component',[
    "id" => "runPlaybookComponent2",
    "title" => "Playbook Çalıştır",
    "footer" => [
        "text" => "Çalıştır",
        "class" => "btn-success",
        "onclick" => "runPlaybook22()"
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
    function getPlaybooks2(){
        /////////////dropdown1/////////////
        $('#textDiv2').html('');
        $('#sudopass_field').val('');
        let form = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request(API('get_playbooks2'), form, function(response) {

        const myArray = response.split("\n");
        document.getElementById("dropdown1").options.length=0;
        for(let i=0;i<myArray.length-1;i++){
            var opt = document.createElement("option");
            opt.text = myArray[i];
            opt.value = myArray[i];
            
            document.getElementById("dropdown1").options.add(opt);
        }
        Swal.close();
        }, function(error) {
            error = JSON.parse(error)["message"]
            showSwal(error,'error');
        });
        /////////////table/////////////
        let data = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request(API('get_log2'), data, function(response) {
            $('#logTable1').html(response).find('table').DataTable(dataTablePresets('normal'));
            Swal.close();
        }, function(error) {
            error = JSON.parse(error)["message"]
            showSwal(error,'error');
        });
        
    }
    
    function showLogContent2(line){
        let fileName = line.querySelector("#name").innerHTML + "-.-" + line.querySelector("#user").innerHTML;
        let formData = new FormData()
        formData.append("fileName",fileName);
        request(API("get_content_log2"), formData, function(response){
            let filecontent = JSON.parse(response).message
            $("#showLogContentComponent2").find('.modal-body').html("<pre style='background-color: #EBECE4; '>"+filecontent+"</pre>");
            $('#showLogContentComponent2').modal("show"); 
            Swal.close();
        },function(response){
            showSwal('{{__("Log göstermede hata oluştu")}}','error');
        });
    }

    function deleteLog2(line){
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
                request(API("delete_log2") ,formData,function(response){
                    getPlaybooks2();
                    showSwal('{{__("Silindi")}}', 'success',2000);
                }, function(response){
                    let error = JSON.parse(response);
                    showSwal(error.message, 'error');
                });
            }
        });
    }

    let isEmpty = function(str) {
        return (str.length === 0 || !str.trim());
    };

    function runPlaybook2() {
        var e = document.getElementById("dropdown1");
        var playbookname=e.options[e.selectedIndex].text;
        test(playbookname);

        $('#playbookTaskModal').on('hidden.bs.modal',  () => {

            showSwal('{{__("Yükleniyor...")}}','info');
            let data = new FormData();
            request(API("run_playbook2"), data, function(response) {
                $("#textDiv2").html(response);   
                Swal.close();  
            }, function(response) {
                $("#textDiv2").html("");
                let error = JSON.parse(response);
                showSwal(error.message, 'error', 3000);
            }); 
        })
        
    }

    function saveLogPlaybook2(){
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
                formData.append("logFileName", result.value);
                request(API("playbook2_save_output") ,formData,function(response){
                    getPlaybooks2();
                    $('#playbookTaskModal').modal('hide');
                    showSwal('{{__("Kaydedildi")}}', 'success',2000);
                }, function(response){
                    let error = JSON.parse(response);
                    showSwal(error.message, 'error', 3000);
                }); 
            }
        });        
    }

</script>
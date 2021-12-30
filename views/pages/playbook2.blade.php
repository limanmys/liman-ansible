<div class="container-fluid mt-5">
    <div class="row">
      <div class="col-sm-2">
        <button class="btn btn-primary mb-2" onclick="runPlaybook2()" type="button" 
        style="width:100%;">
            <i class="far fa-play-circle mr-2"></i> {{ __('Çalıştır') }}
        </button> <br>
        
        <select id="dropdown1" style="width:100%;"></select>
            <div class="mb-1"></div>
        <input type="password" name="sudoPassword" id="sudopass_field" class="container-sm"
            placeholder="Sudo şifresini giriniz" style="width:100%;">
            <br><br>
            <p id="test"></p>
      </div>
      <div class="col-sm-5">
          <div class="col" id="fileTextDiv2">
            <textarea style="width:100%;height: 80%; min-height: 400px;" id="textDiv2"></textarea>
            <button  class="btn btn-primary mb-2 float-right" id="fileEditButton" onclick="saveLogPlaybook2()">
                <i class="fas fa-edit" ></i> {{ __('Kaydet') }}
            </button>
        </div>
      </div>
      <div class="col-sm-5">
        <div id="logTable1"></div>
            @component('modal-component',[
            "id" => "showLogContentComponent2",
            "title" => "Dosya İçeriği"
            ])
            @endcomponent
      </div>
    </div>
  </div>

<script>
    getLog2();
    function getLog2(){
        let form = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request(API('get_log2'), form, function(response) {
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
                    showSwal('{{__("Silindi")}}', 'success',2000);
                }, function(response){
                    let error = JSON.parse(response);
                    showSwal(error.message, 'error');
                });
            }
        });
    }

    function getPlaybooks2(){
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
    }

    let isEmpty = function(str) {
        return (str.length === 0 || !str.trim());
    };

    function runPlaybook2() {
        showSwal('{{__("Yükleniyor...")}}','info');
        let data = new FormData();

        var e = document.getElementById("dropdown1");
        var playbookname=e.options[e.selectedIndex].text;
        data.append('playbookname',playbookname);

        sudopass = $('#sudopass_field').val();
        data.append('sudopass',sudopass);
        
        request(API("run_playbook2"), data, function(response) {
            $("#textDiv2").html(response);   
            Swal.close();  
        }, function(response) {
            $("#textDiv2").html("");
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
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
                //let logContent = $("#playbookTaskModal").find('#outputArea').text();
                formData.append("logFileName", result.value);
                //formData.append("logFileContent", logContent);
                request(API("playbook2_save_output") ,formData,function(response){
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
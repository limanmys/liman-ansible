<button  class="btn btn-primary mb-2" onclick="showModal()" type="button">
    <i class="far fa-play-circle mr-2"></i> {{ __('Çalıştır') }}
</button>

<br>

<select id="dropdown1" style="width:232px;">
</select>

<div class="mb-1"></div>
    
<input type="password" name="sudoPassword" id="sudopass_field" class="container-sm"
placeholder="Sudo şifresini giriniz">
<br><br>

<div class="col" id="fileTextDiv2">
    <textarea style="width:100%;height: 80%; min-height: 400px;" id="textDiv2"></textarea>
    <button  class="btn btn-primary mb-2 float-right" id="fileEditButton" onclick="saveLogPlaybook()">
        <i class="fas fa-edit" ></i> {{ __('Dosya Güncelle') }}
    </button>
</div>

<script>
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

    function showModal() {
        showSwal('{{__("Yükleniyor...")}}','info');
        let data = new FormData();

        var e = document.getElementById("dropdown1");
        var playbookname=e.options[e.selectedIndex].text;
        data.append('playbookname',playbookname);

        sudopass = $('#sudopass_field').val();
        data.append('sudopass',sudopass);
        
        request(API("list_hosts"), data, function(response) {
            $("#textDiv2").html(response);   
            Swal.close();  
        }, function(response) {
            $("#textDiv2").html("");
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
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
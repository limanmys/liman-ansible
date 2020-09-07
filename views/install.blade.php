@if(server()->type != "linux_ssh")
    <div class="alert alert-danger" role="alert">
    {{__("Bu eklentiyi kullanabilmek için anahtarlı bir linux sunucusu gerekmektedir.")}}
    </div>
@php(die())
@endif

<div class="alert alert-warning" role="alert">
  {{__("Bu sunucuda ansible kurulu değil, hemen kurmak için aşağıdaki butonu kullanabilirsiniz.")}}
</div>

<button id="installButton" class="btn btn-secondary" onclick="startInstallation()">{{__("Ansible paketini depodan kur.")}}</button>
<pre id="output"></pre>

<script>
    function startInstallation()
    {
      $("#installButton").attr("disabled","true");
        showSwal('{{__("Yükleniyor...")}}','info',2000);
        request('{{API('installAnsiblePackage')}}', new FormData(), function (response) {
          observeInstallation();
        }, function(response){
            let error = JSON.parse(response);
            showSwal(error.message,'error',2000);
        })
    }

    function observeInstallation()
    {
        request('{{API('observeInstallation')}}', new FormData(), function (response) {
          let json = JSON.parse(response);
          setTimeout(() => {
                observeInstallation();
            }, 2000);
          $("#output").text(json["message"]);
        }, function(response){
            let error = JSON.parse(response);
            showSwal(error.message,'error',2000);
        })
    }
</script>
<div id="hostsDiv"></div>

<script>

    function getHosts(){
        let form = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request("{{API('get_hosts')}}", form, function(response) {
            $('#hostsDiv').html(response);
            Swal.close();
        }, function(error) {
            $('#hostsDiv').html("Hata oluştu");
            Swal.close();
        });
    }
</script>
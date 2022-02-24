<div id="dashboardTable"></div>

<script>
    function getDashboard(){
        let form = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request(API('get_dashboard'), form, function(response) {
            $('#dashboardTable').html(response);
            Swal.close();
        }, function(error) {
            $('#dashboardTable').html("Hata oluştu");
            Swal.close();
        });
    }
    
</script>
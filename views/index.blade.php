<?php 

    $checkPackage = verifyInstallation();
    if(!$checkPackage){ 
    echo "<script>window.location.href = '" . navigate('install') . "';</script>";
    } 


?>

<ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px;">
    <li class="nav-item">
        <a class="nav-link active"  onclick="getHosts()" href="#hosts" data-toggle="tab">Hosts</a>
    </li>
    <li class="nav-item">
        <a class="nav-link "  onclick="getUsers()" href="#users" data-toggle="tab">Kişiler</a>
    </li>
</ul>

<div class="tab-content">
    @include('hosts')    
    <div id="users" class="tab-pane">
    </div>
</div>


<script>

    if(location.hash === ""){
        getHosts();
    }
    
    function getHosts(){
        var form = new FormData();
        showSwal('{{__("Yükleniyor...")}}','info');
        request("{{API('getHosts')}}", form, function(response) {
            $('#hosts').html(response);
            Swal.close();
        }, function(error) {
            $('#hosts').html("Hata oluştu");
            Swal.close();
        });
    }

</script>
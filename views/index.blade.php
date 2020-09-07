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

    <div id="hosts" class="tab-pane active">
    </div>

    <div id="users" class="tab-pane">
    </div>

</div>


<script>

    if(location.hash === ""){
        getHosts();
    }
    
    function getHosts(){
        var form = new FormData();
        request("{{API('getHosts')}}", form, function(response) {
            message = JSON.parse(response)["message"];
            $('#hosts').html(message);
        }, function(error) {
            $('#hosts').html("Hata oluştu");
        });
    }

</script>
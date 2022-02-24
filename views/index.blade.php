<ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px;">
    <li class="nav-item">
        <a class="nav-link active" onclick="getDashboard()" href="#home" data-toggle="tab"><i class="fas fa-home mr-2"></i></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" onclick="getHosts()" href="#hosts" data-toggle="tab"><i class="fas fa-server mr-2"></i>{{ __('Hosts') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" onclick="getFiles()" href="#files" data-toggle="tab"><i class="fas fa-archive mr-2"></i>{{ __('Dosyalar') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" onclick="getPlaybooks()" href="#playbooks" data-toggle="tab"><i class="far fa-play-circle mr-2"></i>{{ __('Playbook') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" onclick="getTemplates()" href="#templates" data-toggle="tab"><i class="fas fa-file-code mr-2"></i>{{ __('Template') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" onclick="getLogs()" href="#logs" data-toggle="tab"><i class="fas fa-file-alt mr-2"></i>{{ __('Loglar') }}</a>
    </li>
</ul>
<div class="tab-content">

    <div id="home" class="tab-pane active">
        @include("pages.dashboard")
    </div>

    <div id="hosts" class="tab-pane">
        @include("pages.host")
    </div>

    <div id="files" class="tab-pane">
        @include("pages.files")
    </div>

    <div id="playbooks" class="tab-pane">
        @include("pages.playbook")
    </div>

    <div id="templates" class="tab-pane">
        @include("pages.templates")
    </div>

    <div id="logs" class="tab-pane">
        @include("pages.logs")
    </div>
</div>

@component('modal-component',[
    "id" => "taskModal",
    "title" => "Görev İşleniyor",
])@endcomponent

@component('modal-component',[
    "id" => "playbookTaskModal",
    "title" => "Görev İşleniyor",
    "footer" => [
        "text" => "Kaydet",
        "class" => "btn-primary",
        "onclick" => "saveLogPlaybook()"
    ]
])@endcomponent

<script>

    $('#taskModal').on('hidden.bs.modal', function (e) {
        $('#taskModal').find('.modal-body').html("");
    })

    if(location.hash === ""){
        getDashboard();
    }

    function onTaskSuccess(){
        showSwal('{{__("İsteğiniz başarıyla tamamlandı...")}}', 'success', 2000);
        setTimeout(function(){
            $('#taskModal').modal("hide"); 
        }, 2000);
    }

    function onTaskFail(){
        showSwal('{{__("İsteğiniz yerine getirilirken bir hata oluştu!")}}', 'error', 2000);
    }

</script>

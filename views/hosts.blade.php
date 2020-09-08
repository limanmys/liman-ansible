<div id="hosts" class="tab-pane active">

    @include('modal-button',[
        "class"     =>  "btn btn-outline-primary",
        "target_id" =>  "addGroupModal",
        "text"      =>  "Grup Ekle",
        "icon" => "fas fa-plus"
    ])<br><br>

    @include('modal',[
        "id"=>"addGroupModal",
        "title" => "Grup Ekleme",
        "url" => API('addGroup'),
        "next" => "reload",
        "inputs" => [
            "Grup Adı" => "groupname:text:Grup Adı",
            "Ip Adresi" => "ipaddress:text:Ip Adresi Giriniz",
        ],
        "submit_text" => "Ekle"
    ])

    <div class="row" style="margin-top: 10px;">
        @if(is_array($data))
            @foreach($data as $source)
            
            <div class="col-md-6 ">
                    <div class="card shadow p-3 mb-5 bg-white rounded" id="loginAttemtChart" >
                        <div class="card-header">
                            <h3 class="card-title">{{$source["name"]}}</h3>
                        </div>
                        <div class="card-body">
                            <div id="accordion">
                                <div class="card card-primary">
                                <div class="card-header">
                                    <h4 class="card-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#{{$source["name"]}}Size" class="collapsed" aria-expanded="false">
                                        İp Adresleri
                                    </a>
                                    </h4>
                                </div>
                                <div id="{{$source["name"]}}Size" class="panel-collapse in collapse" style="">
                                    <div class="card-body">
                                        @foreach ($source["ip"] as $ip)
                                            {{$ip}} &nbsp;&nbsp;   <button class="btn btn-danger btn-xs" onclick="deleteClientIpJS('{{$source['name']}}','{{$ip}}')">x</button>
                                        <br>
                                        @endforeach
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white ">
                            <button class="btn btn-primary " onclick="addClientIpJS('{{$source['name']}}')"><i class='fas fa-plus'></i>  {{__("İp Ekle")}}</button>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
@include('modal',[
    "id"=>"addClientIpModal",
    "title" => "Client Ip Ekleme",
    "url" => API('addClientIp'),
    "next" => "reload",
    "inputs" => [
        "hostsname:hostname" => "hostsname:hidden",
        "Ip Adresi" => "ipaddress:text:Ip Adresi (Örn : 172.0.0.1)",
    ],
    "submit_text" => "Ekle"
])

@include('modal',[
    "id"=>"deleteClientIpModal",
    "title" => "Client Ip Silme",
    "url" => API('deleteClientIp'),
    "next" => "reload",
    "text" => "Bu işlem geri alınamaz. Silmek istediğinize emin misiniz ?",
    "inputs" => [
        "deletehostsname:deletehostsname" => "deletehostsname:hidden",
        "ipaddress:ipaddress" => "ipaddress:hidden",
    ],
    "submit_text" => "Sil"
])


<script>
    function addClientIpJS(name){
        $('[name=hostsname]').val(name);
        $("#addClientIpModal").modal("show");
    }
    function deleteClientIpJS(name,ipaddress){
        $('[name=deletehostsname]').val(name);
        $('[name=ipaddress]').val(ipaddress);
        $("#deleteClientIpModal").modal("show");
    }
</script>
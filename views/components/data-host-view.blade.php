@include('modal-button',[
    "class"     =>  "btn btn-outline-primary",
    "target_id" =>  "addGroupModal",
    "text"      =>  "Grup Ekle",
    "icon" => "fas fa-plus"
])<br><br>

@include('modal',[
    "id"=>"addGroupModal",
    "title" => "Grup Ekleme",
    "url" => API('add_group'),
    "next" => "reload",
    "inputs" => [
        "Grup Adı" => "groupname:text:Grup Adı",
        "Ip Adresi" => "ipaddress:text:Ip Adresi Giriniz",
    ],
    "submit_text" => "Ekle"
])

<div class="row" style="margin-top: 10px;">

    @if(is_array($data))
        @if(empty($data))
            <div class="alert alert-info alert-dismissible col-md-12">
                <h5><i class="icon fas fa-info"></i> Bilgi !</h5>
                    Grup Bulunmamaktadır
            </div>
        <?php exit(); ?>
        @endif
        @foreach($data as $source)
        
        <div class="col-md-6 ">
                <div class="card shadow p-3 mb-5 bg-white rounded" id="loginAttemtChart" >
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">{{\Illuminate\Support\Str::title($source["name"])}}</h3>
                    </div>
                    <div class="card-body">
                        <div id="accordion">
                            <div class="card card-primary">
                            <div class="card-header">
                                <h4 class="card-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#{{$source["name"]}}Size" class="collapsed" aria-expanded="false">
                                    Client Adresleri
                                </a>
                                </h4>
                            </div>
                            <div id="{{$source["name"]}}Size" class="panel-collapse in collapse">
                                <div class="card-body">
                                    <table class="table" style="text-align: center">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Ip</th>
                                                <th scope="col">Sil</th>
                                                <th scope="col">Ssh İd Ekle</th>
                                            </tr>
                                        </thead>
                                        @foreach ($source["ip"] as $key => $ip)
                                            
                                            <tr> 
                                                <th scope="row">{{$key+1}}</th>
                                                <td> {{$ip}}  </td>
                                                @if(!strpos(trim($ip),'bulunmamaktadır') !== FALSE)
                                                    <td><button class="btn btn-danger btn-xs" onclick="deleteClientIpJS('{{$source['name']}}','{{$ip}}')"><i class="fas fa-times"></i></button></td>
                                                @endif
                                                <td><button class="btn btn-primary btn-xs" onclick="deleteClientIpJS('{{$source['name']}}','{{$ip}}')"><i class="fas fa-plus"></i></button></td>
                                            </tr>
                                        @endforeach
                                    </table>
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
@include('modal',[
    "id"=>"addClientIpModal",
    "title" => "Client Ip Ekleme",
    "url" => API('add_host'),
    "next" => "reload",
    "inputs" => [
        "hostsname:hostname" => "hostsname:hidden",
        "Ip Adresi" => "ipaddress:text:Ip Adresi (Örn : 172.0.0.1)",
    ],
    "submit_text" => "Ekle"
])


<script>

    function addClientIpJS(name){
        $('[name=hostsname]').val(name);
        $("#addClientIpModal").modal("show");
    }

    function deleteClientIpJS(name,ipaddress){
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
                let formData = new FormData();
                formData.append("deletehostsname",name);
                formData.append("ipaddress",ipaddress);
                request(API("delete_ip") ,formData,function(response){
                    showSwal('{{__("Silindi")}}', 'success',2000);
                    reload();
                }, function(response){
                    let error = JSON.parse(response);
                    showSwal(error.message, 'error');
                });
            }
        });
    }
</script>
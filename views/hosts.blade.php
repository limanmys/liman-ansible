<div id="hosts" class="tab-pane active">
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
                                            {{$ip}}<br>
                                        @endforeach
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white ">
                            <button class="btn btn-primary " onclick=""><i class='far fa-eye'></i>  {{__("İp Ekle")}}</button>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

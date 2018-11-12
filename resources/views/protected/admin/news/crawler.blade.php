@extends('protected.admin.includes.layout')
{{--include extra css--}}
@section('outCSS')
    <link href="{{asset('assets/css/icheck/flat/green.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/datatables/tools/css/dataTables.tableTools.css')}}" rel="stylesheet">
@stop

@section('content')
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>{{trans('label.admin.news.title')}}<small>{{ trans('label.list') }}</small></h2>

                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-6">&nbsp;</div>
                    <div class="col-md-3">
                        <div class="row">
                            <div class="col-md-12 text-right" style="padding-top:5px">Portal Berita</div>
                            <div class="col-md-12">
                                <select name="portal" id="portal" class="form-control" placeholder="Portal Berita">
                                    <option>-Pilih-</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="row">
                            <div class="col-md-12 text-right" style="padding-top:5px">Bulan</div>
                            <div class="col-md-12">
                                <select name="bulan" id="bulan" class="form-control" placeholder="Bulan">
                                    <option>-Pilih-</option>
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{$i}}">{{$i}}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="row">
                            <div class="col-md-12 text-right" style="padding-top:5px">Tahun</div>
                            <div class="col-md-12">
                                <select name="tahun" id="tahun" class="form-control" placeholder="Tahun">
                                    <option>-Pilih-</option>
                                    @for ($i = (date('Y')-4); $i <= date('Y'); $i++)
                                        <option value="{{$i}}">{{$i}}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="row">
                            <div class="col-md-12 text-right" style="padding-top:5px">&nbsp;</div>
                            
                               <button type="button" class="btn btn-md btn-success col-md-12">
                                   <i class="fa fa-search"></i> Search
                                </button>
                            
                        </div>
                    </div>
                </div>
            </div>
            @if(Session::has('success'))
            <div class="alert alert-info alert-dismissible" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  {{Session::get('success')}}
            </div>
            @endif
            <div class="x_content">
                <div id="data" class="text-center">Silahkan Pilih Portal dan Bulan Terlebih Dahulu</div>
            </div>
        </div>
    </div>

    <br />
    <br />
    <br />

</div>

@stop

@section('outJS')
    <!-- Datatables -->
    <script src="{{asset('assets/js/datatables/js/jquery.dataTables.js') }}"></script>
    <script src="{{asset('assets/js/datatables/tools/js/dataTables.tableTools.js')}}"></script>
    <script src="{{asset('assets/js/admin/news.js')}}"></script>
    <script>
        var APP_URL='{{url("/")}}';
        function loaddata()
        {
            var porta
            $('#data').load(APP_URL + 'admin/data/'+);
        }
    </script>
@stop

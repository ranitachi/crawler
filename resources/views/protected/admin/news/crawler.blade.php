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
                    <div class="col-md-5">&nbsp;</div>
                    <div class="col-md-3">
                        <div class="row">
                            <div class="col-md-12 text-right" style="padding-top:5px">Portal Berita</div>
                            <div class="col-md-12">
                                <select name="portal" id="portal" class="form-control" placeholder="Portal Berita">
                                    <option value="-1">-Pilih-</option>
                                    @foreach ($order as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="row">
                            <div class="col-md-12 text-right" style="padding-top:5px">Bulan</div>
                            <div class="col-md-12">
                                <select name="bulan" id="bulan" class="form-control" placeholder="Bulan">
                                    <option value="-1">-Pilih-</option>
                                    @for ($i = 1; $i <= 12; $i++)
                                        @if (date('n')==$i)
                                            <option value="{{$i}}" selected="selected">{{toMonth($i)}}</option>
                                        @else
                                            <option value="{{$i}}">{{toMonth($i)}}</option>
                                        @endif
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
                                    <option value="-1">-Pilih-</option>
                                    @for ($i = (date('Y')-6); $i <= date('Y'); $i++)
                                        @if (date('Y')==$i)
                                            <option value="{{$i}}" selected="selected">{{$i}}</option>
                                        @else
                                            <option value="{{$i}}">{{$i}}</option>
                                        @endif
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
                <div class="row">
                    <div class="col-md-9"></div>
                    <div class="col-md-3">
                        <form class="example">
                            <input type="text" placeholder="Search.." name="search" id="search" onkeyup="caridata(this.value)">
                        </form>
                    </div>
                </div>
                <div id="data" class="text-center" style="position: relative;">
                    @include('protected.admin.news.data')
                </div>
            </div>
        </div>
    </div>


</div>

@stop

@section('outJS')
    <!-- Datatables -->
    {{-- <script src="{{asset('assets/js/datatables/js-new/datatables.js') }}"></script> --}}
    <script src="{{asset('assets/js/datatables/js-new/jquery.highlight.js') }}"></script>
    <script src="{{asset('assets/js/datatables/js-new/dataTables.highlight.js')}}"></script>
    {{-- <link ref="stylesheet" href="{{asset('assets/js/datatables/js-new/datatables.css')}}">
    <link ref="stylesheet" href="{{asset('assets/js/datatables/js-new/dataTables.highlights.css')}}"> --}}
    {{-- <script src="{{asset('assets/js/datatables/js/jquery.dataTables.js') }}"></script>
    <script src="{{asset('assets/js/datatables/tools/js/dataTables.tableTools.js')}}"></script> --}}
    <script src="{{asset('assets/js/datepicker/daterangepicker.js')}}"></script>
    {{-- <script src="{{asset('assets/js/admin/news.js')}}"></script> --}}
    <script>
        var APP_URL='{{url("/")}}';

        $(function() {
            $('body').on('click', '.pagination a', function(e) {
                e.preventDefault();

                $('#load a').css('color', '#dfecf6');
                // $('#load').append('<img style="position: absolute; left: 0; top: 0; z-index: 100000;" src="/images/loading.gif" />');

                var url = $(this).attr('href');  
                var search = $('#search').val();
                if(search!='')
                    getArticles(url+'&key='+search);
                else
                    getArticles(url);
                // alert(url);
                window.history.pushState("", "", url);
            });

            function getArticles(url) {
                $.ajax({
                    url : url  
                }).done(function (data) {
                    $('#data').html(data);  
                }).fail(function () {
                    alert('Data could not be loaded.');
                });
            }
        });

        function caridata(val)
        {
            var url=APP_URL+'/admin/news';
            $.ajax({
                url : url  ,
                data : {key:val}
            }).done(function (data) {
                $('#data').html(data);  
            }).fail(function () {
                alert('Data could not be loaded.');
            });
        }

        // loaddata();
        
        // function loaddata()
        // {
        //     var portal=$('#portal').val();
        //     var bulan=$('#bulan').val();
        //     var tahun=$('#tahun').val();
        //     $('#data').load(APP_URL + '/admin/data/'+bulan+'-'+tahun+'/'+portal,function(){
        //         // var table=$('#news').DataTable();
        //         //table.search( 'tempo' ).draw();
        //         // $("#pilihsemua").click(function(){
        //         //     $('input:checkbox').not(this).prop('checked', this.checked);
        //         // });
        //     });
        // }

        function showmodal(id)
        {
            $('#konten-berita').text('');
            $.ajax({
                url : SITE_ROOT+'get-konten/'+id,
                success : function(res){
                    $('#konten-berita').html(res.konten);
                    $('#url_berita').val(res.url);
                    $('#id_berita').val(id);
                    $('#judul').val(res.judul);
                }
            });
            $('#modal-add').modal('show');
        }

        function getkabupaten(idprov)
        {
            $('#kab').load(APP_URL+'/kabupaten-by/'+idprov);
        }
    </script>
    <style>
        /* Style the search field */
        form.example input[type=text] {
        padding: 10px;
        font-size: 17px;
        border: 1px solid grey;
        float: left;
        width: 100%;
        background: #f1f1f1;
        }

        /* Style the submit button */
        form.example button {
        float: left;
        width: 20%;
        padding: 10px;
        background: #2196F3;
        color: white;
        font-size: 17px;
        border: 1px solid grey;
        border-left: none; /* Prevent double borders */
        cursor: pointer;
        }

        form.example button:hover {
        background: #0b7dda;
        }

        /* Clear floats */
        form.example::after {
        content: "";
        clear: both;
        display: table;
        }
    </style>
@stop
@section('modal')
    <div class="modal fade" id="modal-add" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" style="width:90% !important;">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Proses Berita</h4>
				</div>
				<div class="modal-body">
					<form action="{{ url('admin/proses-berita') }}" method="POST" enctype="multipart/form-data">
						<input type="hidden" name="_Token" value="{{ csrf_token() }}">
						<div class="row">
                            <div class="col-md-8" style="padding:0px 20px 0px 0px;">
                                <h3>Konten Berita</h3>
                                <div id="konten-berita" style="border:1px solid #ddd;padding:15px"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kategori</label>
                                    <select class="form-control" name="kategori" placeholder="Kategori">
                                        <option value="-1">-Pilih-</option>
                                        @foreach ($kategori as $item)
                                            <option value="{{$item->id}}">{{$item->kategori}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Provinsi</label>
                                            <select class="form-control" name="provinsi" placeholder="Provinsi" onchange="getkabupaten(this.value)">
                                                <option value="-1">-Pilih-</option>
                                                @foreach ($provinsi as $item)
                                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Kabupaten</label>
                                            <div id="kab">
                                                <select class="form-control" name="kabupaten" placeholder="Kabupaten">
                                                    <option value="-1">-Pilih-</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Lokasi Kejadi</label>
                                            <input type="text" class="form-control" name="lokasi" placeholder="Lokasi">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tanggal Kejadian</label>
                                                <input type="date" class="form-control" name="tanggal_kejadian">
                                            </div>
                                        </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Korban Meninggal</label>
                                            <input type="text" class="form-control" name="korban_meninggal" placeholder="Jumlah" value="0">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Korban Luka</label>
                                                <input type="text" placeholder="Jumlah" class="form-control" name="korban_luka" value="0">
                                            </div>
                                        </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Bangunan Rusak</label>
                                                <input type="text" placeholder="Jumlah" class="form-control" name="bangunan_rusak" value="0">
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="url_berita" id="url_berita">
                                    <input type="hidden" name="id_berita" id="id_berita">
                                    <input type="hidden" name="judul" id="judul">
                                </div>
                                
                            </div>
                        
						
				</div>
				<div class="modal-footer">
					<button type="button" data-dismiss="modal" class="btn btn-default">Batal</button>
					<input type="submit" class="btn btn-success" value="Simpan">
				</div>
				</form>
			</div>
		</div>
	</div>
@stop

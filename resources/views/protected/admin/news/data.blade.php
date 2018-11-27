
<table id="news" class="table table-striped responsive-utilities jambo_table">
    <thead>
        <tr class="headings">
            <th>No</th>
            <th>Portal</th>
            <th>Judul</th>
            <th>Tanggal Berita</th>
            <th>Aksi</th>
            {{-- <th>Aksi<br><input type="checkbox" name="pilihasemua" id="pilihsemua"></th> --}}
        </tr>
    </thead>

    <tbody>
        
        @foreach ($data as $key=> $item)
            <tr class="even pointer">
                <td class="text-center">{{$key+1}}</td>
                <td class="">{{$item->portal->name}}</td>
                <td class="text-left">{{$item->judul=='' ? $item->url : $item->judul}}</td>
                <td class="text-left">{{date('d-m-Y',strtotime($item->tanggal))}}</td>
                <td class="text-center">
                    <a href="javascript:showmodal({{$item->id}})"><i class="fa fa-list"></i></a>
                </td>
                {{-- <td><input type="checkbox" name="pilih[{{$item->id}}]"></td> --}}
            </tr>
        @endforeach
    </tbody>
</table>

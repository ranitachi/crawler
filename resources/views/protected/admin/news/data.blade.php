
<table id="" class="table table-striped responsive-utilities">
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
        <?php
            $no=((isset($_GET['page']) ? $_GET['page'] : 1) * 20) -19;
        ?>
        @foreach ($data as $key=> $item)
            @if (!in_array($item->id,$idberita))
            
            <tr class="even pointer">
                <td class="text-center">{{$no}}</td>
                <td class="">{{$item->portal->name}}</td>
                <td class="text-left">{{$item->judul=='' ? $item->url : $item->judul}}</td>
                <td class="text-left">{{date('d-m-Y',strtotime($item->tanggal))}}</td>
                <td class="text-center">
                    <a href="javascript:showmodal({{$item->id}})"><i class="fa fa-list"></i></a>
                </td>
                {{-- <td><input type="checkbox" name="pilih[{{$item->id}}]"></td> --}}
            </tr>
            <?php
                $no++;
            ?>
            @endif
        @endforeach
    </tbody>
</table>
{!! $data->render() !!}

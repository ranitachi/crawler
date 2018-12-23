
<table id="" class="table table-striped responsive-utilities">
    <thead>
        <tr class="headings">
            <th class="text-center">No</th>
            <th class="text-center">Jenis Kejadian</th>
            <th class="text-center">Lokasi</th>
            <th class="text-center">Tanggal Kejadian</th>
            <th class="text-center">Aksi</th>
            {{-- <th>Aksi<br><input type="checkbox" name="pilihasemua" id="pilihsemua"></th> --}}
        </tr>
    </thead>

    <tbody>
        <?php
            $no=((isset($_GET['page']) ? $_GET['page'] : 1) * 20) -19;
            // dd($data);
        ?>
        @foreach ($data as $key=> $item)
            <tr class="even pointer">
                <td class="text-center">{{$no}}</td>
                <td class="">{{$item->jnskategori->kategori}}</td>
                <td class="text-left">{{$item->lokasi}}</td>
                <td class="text-left">{{date('d-m-Y',strtotime($item->tanggal_kejadian))}}</td>
                <td class="text-center">
                    <a href="javascript:showmodal({{$item->id}})"><i class="fa fa-list"></i></a>
                </td>
                {{-- <td><input type="checkbox" name="pilih[{{$item->id}}]"></td> --}}
            </tr>
            <?php
                $no++;
            ?>
        @endforeach
    </tbody>
</table>
{!! $data->render() !!}

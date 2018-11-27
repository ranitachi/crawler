<?php

namespace App\Http\Controllers;
use App\Models\Provinsi;
use App\Models\Kabupaten;
class PagesController extends Controller
{

    /**
     * @return \Illuminate\View\View
     */
    public function getHome()
    {
        return redirect()->route('admin_dashboard');
    }
    public function kabupaten_by($idprov)
    {
        $kabupaten=Kabupaten::where('province_id',$idprov)->get();
        echo '<select class="form-control" name="kabupaten" placeholder="Kabupaten">
            <option value="-1">-Pilih-</option>';
            foreach ($kabupaten as $item):
                echo '<option value="'.$item->id.'">'.$item->name.'</option>';
            endforeach;
        echo '</select>';
    }
}

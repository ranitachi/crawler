<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BeritaResult extends Model
{
    use SoftDeletes;
    protected $table='berita_result';
    protected $fillable=['id_berita','kategori','provinsi','kabupaten','lokasi','tanggal_kejadian','meninggal','luka','bangunan_rusak','url_berita','judul'];
    protected $hidden=['created_at','updated_at','deleted_at'];

    function jnskategori()
    {
        return $this->belongsTo('App\Models\Kategori','kategori');
    }
    function berita()
    {
        return $this->belongsTo('App\Models\News','id_berita');
    }
    function provinsi()
    {
        return $this->belongsTo('App\Models\Provinsi','provinsi');
    }
    function kabupaten()
    {
        return $this->belongsTo('App\Models\Kabupaten','kabupaten');
    }
}

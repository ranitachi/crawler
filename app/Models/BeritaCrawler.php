<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BeritaCrawler extends Model
{
    use SoftDeletes;
    protected $table='berita_crawler';
    protected $fillable=['portal_id','url','file','isi','tanggal','judul','created_at','updated_at','deleted_at'];
    function portal()
    {
        return $this->belongsTo('App\Models\Order','portal_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PagingSetting extends Model
{
    use SoftDeletes;
    protected $table='paging_setting';
    protected $fillable=['order_id','tag','name','html','count_of_page','created_at','updated_at','deleted_at'];

    public function order() {
        return $this->belongsTo(Order::class, 'order_id');
    }
}

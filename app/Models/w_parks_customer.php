<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class w_parks_customer extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function customers()
    {
        return $this->belongsTo(customers::class,'customer_id');
    }

    public function parkings()
    {
        return $this->belongsTo(parkings::class,'parking_id');
    }

    public function garages()
    {
        return $this->belongsTo(garages::class,'garage_id');
    }

    public function floors()
    {
        return $this->belongsTo(floors::class,'floor_id');
    }
}

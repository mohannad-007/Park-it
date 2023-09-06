<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class w_parks extends Model
{
    use HasFactory;

    protected $guarded=[];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
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

    public function cars()
    {
        return $this->belongsTo(cars::class,'car_id');
    }

    public function w_invoices(): HasMany
    {
        return $this->hasMany(w_invoices::class);
    }

}

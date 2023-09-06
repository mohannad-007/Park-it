<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class floors extends Model
{
    use HasFactory;
    protected $guarded=[];
//    new

    public function garages()
    {
        return $this->belongsTo(garages::class,'garage_id');
    }

//    new


    public function parkings(): HasMany
    {
        return $this->hasMany(parkings::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(reservations::class);
    }

    public function w_parks(): HasMany
    {
        return $this->hasMany(w_parks::class);
    }

    public function w_parks_customer(): HasMany
    {
        return $this->hasMany(w_parks_customer::class);
    }

}

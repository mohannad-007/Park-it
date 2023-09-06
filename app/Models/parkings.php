<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class parkings extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $fillable = [
        'id',
        'number',
        'floors_id',
        'status_id',

    ];
    public function floors()
    {
        return $this->belongsTo(floors::class);
    }

    public function status()
    {
        return $this->belongsTo(status::class);
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

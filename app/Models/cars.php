<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class cars extends Model
{
    use HasFactory;
//    protected $fillable =;
    protected $guarded=[];

    /**
     * Get the user that owns the cars.
     */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
//new
    public function car_types()
    {
        return $this->belongsTo(car_types::class);
    }
//new

//    --------------------

//    public function customers(): HasMany
//    {
//        return $this->hasMany(customers::class);
//    }

    public function reservations(): HasMany
    {
        return $this->hasMany(reservations::class);
    }

    public function w_parks(): HasMany
    {
        return $this->hasMany(w_parks::class);
    }

    public function active_users(): HasOne
    {
        return $this->hasOne(active_users::class);
    }

}

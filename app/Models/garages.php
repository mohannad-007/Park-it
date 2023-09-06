<?php

namespace App\Models;

//use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;


class garages extends Authenticatable
{
    use HasFactory,HasApiTokens;
    protected $guarded=[];
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'floor_number',
        'is_open',
        'price_per_hour',
        'parks_number',
        'time_open',
        'time_close',
        'garage_information',
        'garage_locations_id',
    ];
//    protected $hidden = [
//        'password',
//        'remember_token',
//    ];
//
//    protected $casts = [
//        'email_verified_at' => 'datetime',
//        'password' => 'hashed',
//    ];

//new

    public function garage_location()
    {
        return $this->belongsTo(garage_location::class,'garage_locations_id');
    }


    public function floors(): HasMany
    {
        return $this->hasMany(floors::class,'garage_id');
    }

//new

    public function services(): HasMany
    {
        return $this->hasMany(services::class,"garage_id");
    }

    public function garage_emp(): HasMany
    {
        return $this->hasMany('App\Models\garage_employees', 'garage_id');

    }
//    public function subscriptions(): HasMany
//    {
//        return $this->hasMany(subscriptions::class);

//    }

    public function customers(): HasMany
    {
        return $this->hasMany(customers::class);
    }

    public function fav_garage(): HasMany
    {
        return $this->hasMany(fav_garage::class);
    }

    public function garage_subscriptions(): HasMany
    {
        return $this->hasMany(garage_subscriptions::class,'garage_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(reports::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(reservations::class);
    }



    public function w_parks(): HasMany
    {
        return $this->hasMany(w_parks::class,'garage_id');
    }

    public function w_parks_customer(): HasMany
    {
        return $this->hasMany(w_parks_customer::class,'garage_id');
    }

//    public function active_users(): HasOne
//    {
//        return $this->hasOne(active_users::class,'garage_id');
//    }


    public function active_users(): HasOne
    {
        return $this->hasOne(active_users::class);
    }
    public function active_customer(): HasOne
    {
        return $this->hasOne(active_customers::class);
    }


}

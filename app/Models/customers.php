<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class customers extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function garages()
    {
        return $this->belongsTo(garages::class,'garage_id');
    }

//    public function cars()
//    {
//        return $this->belongsTo(cars::class);
//    }

    public function customer_subscriptions(): HasMany
    {
        return $this->hasMany(customer_subscriptions::class);
    }

    public function w_parks_customer(): HasMany
    {
        return $this->hasMany(w_parks_customer::class);
    }
    public function active_customer(): HasOne
    {
        return $this->hasOne(active_customers::class);
    }

    public function required_serv_cus(): HasMany
    {
        return $this->hasMany(required_serv_cus::class);
    }
    public function w_invoices(): HasMany
    {
        return $this->hasMany(w_customer_invoices::class);
    }

}

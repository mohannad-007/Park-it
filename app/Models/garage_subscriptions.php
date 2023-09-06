<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class garage_subscriptions extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function garages()
    {
        return $this->belongsTo(garages::class,'garage_id');
    }

    public function subscriptions()
    {
        return $this->belongsTo(subscriptions::class,'subscription_id');
    }




    public function user_subscriptions(): HasMany
    {
        return $this->hasMany(user_subscriptions::class);
    }

////    new
//    public function user_subscriptions()
//    {
//        return $this->belongsTo(user_subscriptions::class);
//    }

    public function customer_subscriptions(): HasMany
    {
        return $this->hasMany(customer_subscriptions::class);
    }

}

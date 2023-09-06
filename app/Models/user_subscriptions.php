<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class user_subscriptions extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function users()
    {
        return $this->belongsTo(User::class,'user_id');
    }

//    public function subscriptions()
//    {
//        return $this->belongsTo(subscriptions::class);
//    }

    public function garage_subscriptions()
    {
        return $this->belongsTo(garage_subscriptions::class,'garage_subscriptions_id');
    }






    public function reports(): HasMany
    {
        return $this->hasMany(reports::class);
    }

////    new
//    public function garage_subscriptions(): HasMany
//    {
//        return $this->hasMany(garage_subscriptions::class);
//    }
////new

}

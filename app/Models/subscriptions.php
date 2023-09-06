<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class subscriptions extends Model
{
    use HasFactory;
    protected $guarded=[];

//    public function garages()
//    {
//        return $this->belongsTo(garages::class);
//    }

//    public function user_subscriptions(): HasMany
//    {
//        return $this->hasMany(user_subscriptions::class);
//    }

    public function garage_subscriptions(): HasMany
    {
        return $this->hasMany(garage_subscriptions::class);
    }
}

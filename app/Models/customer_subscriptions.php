<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class customer_subscriptions extends Model
{
    use HasFactory;
    protected $guarded=[];


    public function customers()
    {
        return $this->belongsTo(customers::class);
    }

    public function garage_subscriptions()
    {
        return $this->belongsTo(garage_subscriptions::class);
    }
}

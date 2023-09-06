<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reports extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function reservations()
    {
        return $this->belongsTo(reservations::class);
    }

    public function required_services()
    {
        return $this->belongsTo(required_services::class);
    }

    public function user_subscriptions()
    {
        return $this->belongsTo(user_subscriptions::class);
    }

    public function pay_fees()
    {
        return $this->belongsTo(pay_fees::class);
    }

    public function garages()
    {
        return $this->belongsTo(garages::class);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class garage_location extends Model
{
    use HasFactory;

    protected $guarded = [];


//    public function garages(): BelongsTo
//    {
//        return $this->belongsTo(garages::class);
//    }

    public function garages(): HasOne
    {
        return $this->hasOne(garages::class);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class pay_fees extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function walletes()
    {
        return $this->belongsTo(walletes::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservations()
    {
        return $this->belongsTo(reservations::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(reports::class);
    }

}

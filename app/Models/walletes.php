<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class walletes extends Model
{
    use HasFactory;
    protected $guarded=[];
//new
    public function user(): HasMany
    {
        return $this->hasMany(User::class);
    }
//new
    public function pay_fees(): HasMany
    {
        return $this->hasMany(pay_fees::class);
    }

    public function w_invoices(): HasMany
    {
        return $this->hasMany(w_invoices::class);
    }





}

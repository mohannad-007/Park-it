<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class garage_employees extends Authenticatable
{
    use HasFactory,HasApiTokens;
    protected $guarded=[];

    public function garages()
    {
        return $this->belongsTo(garages::class);
    }
}

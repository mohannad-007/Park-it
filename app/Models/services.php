<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class services extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function garages()
    {
        return $this->belongsTo(garages::class,'garage_id');
    }


    public function required_services(): HasMany
    {
        return $this->hasMany(required_services::class);
    }

    public function required_serv_cus(): HasMany
    {
        return $this->hasMany(required_serv_cus::class);
    }

}

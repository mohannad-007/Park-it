<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class active_users extends Model
{
    use HasFactory;
//    protected $table='active_users';
//    protected $primaryKey='id';
//    public $timestamps='true';

    protected $guarded=[];

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function garages(): BelongsTo
    {
        return $this->belongsTo(garages::class,'garage_id');
    }

    public function cars(): BelongsTo
    {
        return $this->belongsTo(cars::class,'car_id');
    }
}

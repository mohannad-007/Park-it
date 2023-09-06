<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class reservations extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded=[];



    protected $dates = ['deleted_at'];

//    protected static function boot()
//    {
//        parent::boot();
//        static::deleting(function ($reservation) {
//            // تحديد السجلات التي تحتاج إلى التحديث
//            $reservationsToUpdate = self::where('time_end', '<=', now())
//                ->whereNull('deleted_at')
//                ->get();
//
//            // تحديث الحقل المسؤول عن حالة الحذف Soft Delete
//            $reservationsToUpdate->each(function ($reservationUpdate) {
//                $reservationUpdate->delete();
//            });
//        });
//    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parkings()
    {
        return $this->belongsTo(parkings::class,'parking_id');
    }

    public function garages()
    {
        return $this->belongsTo(garages::class,'garage_id');
    }

    public function cars()
    {
        return $this->belongsTo(cars::class,'car_id');
    }

    public function floors()
    {
        return $this->belongsTo(floors::class,'floor_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(reports::class);
    }

    public function pay_fees(): HasMany
    {
        return $this->hasMany(pay_fees::class);
    }
}

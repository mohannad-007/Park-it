<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
//    protected $fillable = [
//        'name',
//        'email',
//        'password',
//    ];

    protected $guarded=[];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

//    public function cars():HasMany{
//      return $this->hasMany(cars::class);
//    };

    /**
     * Get the cars for the user.
     */
//new
    public function walletes()
    {
        return $this->belongsTo(walletes::class,'wallet_id');
    }

    public function getWalletBalance()
    {
        return $this->walletes->price ?? 0;
    }
//    new

    public function cars(): HasMany
    {
        return $this->hasMany(cars::class);
    }



    public function user_subscriptions(): HasMany
    {
        return $this->hasMany(user_subscriptions::class);
    }

    public function required_services(): HasMany
    {
        return $this->hasMany(required_services::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(reservations::class);
    }

    public function w_parks(): HasMany
    {
        return $this->hasMany(w_parks::class);
    }

    public function fav_garage(): HasMany
    {
        return $this->hasMany(fav_garage::class);
    }

    public function pay_fees(): HasMany
    {
        return $this->hasMany(pay_fees::class);
    }



//    public function active_users(): HasOne
//    {
//        return $this->hasOne(active_users::class);
//    }

    public function active_users(): HasMany
    {
        return $this->hasMany(active_users::class);
    }

    public function w_invoices(): HasMany
    {
        return $this->hasMany(w_invoices::class);
    }

}

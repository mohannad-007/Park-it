<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class w_customer_invoices extends Model
{
    use HasFactory;
    public function customer()
    {
        return $this->belongsTo(customers::class);
    }
}

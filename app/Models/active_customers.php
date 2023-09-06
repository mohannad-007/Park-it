<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class active_customers extends Model
{
    use HasFactory;
    public function customers(): BelongsTo
    {
        return $this->belongsTo(customers::class);
    }

    public function garages(): BelongsTo
    {
        return $this->belongsTo(garages::class);
    }
}

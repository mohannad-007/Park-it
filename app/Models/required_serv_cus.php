<?php

namespace App\Models;

use App\Http\Controllers\CustomersController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class required_serv_cus extends Model
{
    use HasFactory;
    protected $guarded=[];
    public $table = "required_serv_cus";



    public function services()
    {
        return $this->belongsTo(services::class);
    }

    public function customers()
    {
        return $this->belongsTo(customers::class,'customer_id');
    }

}

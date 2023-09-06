<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Employees extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id'=>$this->id,
            'name'=>$this->name,
        ];
    }
    public function toArray1()
    {

        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'phone_number'=>$this->phone_number,
            'image'=>$this->image,
            'email'=>$this->email,
            'address'=>$this->address,

        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Garages extends JsonResource
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
            'email'=>$this->email,
            'floor_number'=>$this->floor_number,
            'is_open'=>$this->is_open,
            'price_per_hour'=>$this->price_per_hour,
            'parks_number'=>$this->parks_number,
            'time_open'=>$this->time_open,
            'time_close'=>$this->time_close,
            'garage_information'=>$this->garage_information,
            'garage_locations_id'=>$this->garage_locations_id,
            ''

        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class GarageSubscription extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        $result = DB::table('garage')
//            ->join('status', 'garage.status_id', '=', 'status.id')
//            ->select('status.name')
//            ->where('garage.id', '=', $garageId)
//            ->get();
        $result = DB::table('subscriptions')
            ->join('garage_subscriptions', 'garage_subscriptions.subscription_id', '=', 'subscriptions.id')
            ->select('subscriptions.type')
            ->where('garage_subscriptions.id', '=', $this->id)
            ->get();
        return [
            'id'=>$this->id,
            'price'=>$this->price,
            'subscription' => $result,
        ];
    }
}

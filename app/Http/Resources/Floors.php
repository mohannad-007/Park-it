<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Floors extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */public function toArray(Request $request): array
{
    $data = [];
    $floors = collect($this->resource);

    $totalAvailableCount = 0; // إجمالي عدد الصفات المتاحة
    $totalUnavailableCount = 0; // إجمالي عدد الصفات غير المتاحة

    foreach ($floors as $floor) {
        $floorData = [];

        foreach ($floor->parkings as $parking) {
            $parkingData = [
                'name' => $parking->number,
                'state' => $parking->status->name,
            ];

            if ($parking->status->name === 'available') {
                $totalAvailableCount++;
            } else {
                $totalUnavailableCount++;
            }

            $floorData[] = $parkingData;
        }

        $data['floor' . $floor->number] = $floorData;
    }

    $totalStats = [
        'total_available_count' => $totalAvailableCount,
        'total_unavailable_count' => $totalUnavailableCount,
    ];

    return [
        'data' => $data,
        'total_stats' => $totalStats,
    ];
}




    public function toArray1(): array //لعرض كل الطوابق
    {
        return [
            'id'=>$this->id,
            'number'=>$this->number,
            'garage_id'=>$this->garage_id,


        ];

    }

}

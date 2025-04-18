<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CropsProductionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'year' => $this->year,
            'province' => $this->province,
            'production' => $this->production,
            'vegetable' => $this->vegetable,
            'planted_area' => $this->planted_area,
            'harvested_area' => $this->harvested_area,
            'fertilizer_type' => $this->fertilizer_type,
            'fertilizer_amount' => $this->fertilizer_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
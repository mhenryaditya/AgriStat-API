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
            'uuid' => $this->uuid,
            'year' => $this->year,
            'province' => $this->vegetable,
            'production' => $this->production,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

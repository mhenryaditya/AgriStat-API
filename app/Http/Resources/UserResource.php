<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'kode_pegawai' => $this->kode_pegawai,
            'name' => $this->name,
            'email' => $this->email,
            'img_profile' => $this->img_profile,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CropsProduction extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'crops_production';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'year',
        'province',
        'vegetable',
        'production',
        'planted_area',
        'harvested_area',
        'fertilizer_type',
        'fertilizer_amount',
    ];

    protected $casts = [
        // 'year' => 'integer',
        'production' => 'float',
        'planted_area' => 'float',
        'harvested_area' => 'float',
        'fertilizer_amount' => 'float',
    ];
}

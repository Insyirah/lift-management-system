<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lift extends Model
{
    protected $fillable = [
        'building_id',
        'lift_code',
        'lift_type',
        'brand',
        'model',
        'serial_number',
        'capacity',
        'installation_date',
        'status',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'capacity' => 'integer',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    public function latestInspection(): HasOne
    {
        return $this->hasOne(Inspection::class)->latestOfMany('inspection_date');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    protected $fillable = [
        'organisation_id',
        'name',
        'address',
        'number_of_floors',
        'year_built',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'number_of_floors' => 'integer',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function lifts(): HasMany
    {
        return $this->hasMany(Lift::class);
    }
}

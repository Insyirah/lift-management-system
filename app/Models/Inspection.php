<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Inspection extends Model
{
    protected $fillable = [
        'lift_id',
        'user_id',
        'assigned_by',
        'inspection_date',
        'next_due_date',
        'inspection_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'next_due_date' => 'date',
    ];

    public function lift(): BelongsTo
    {
        return $this->belongsTo(Lift::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(InspectionResult::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(InspectionReport::class);
    }

    public function getOverallResultAttribute(): string
    {
        $results = $this->results;
        if ($results->where('result', 'fail')->count() > 0) {
            return 'fail';
        }
        return 'pass';
    }
}

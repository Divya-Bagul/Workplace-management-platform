<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Desk extends Model
{
    protected $fillable = ['floor_id', 'code', 'status', 'notes'];

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(DeskAllocation::class);
    }

    public function onboardingRequests(): HasMany
    {
        return $this->hasMany(OnboardingRequest::class);
    }
}

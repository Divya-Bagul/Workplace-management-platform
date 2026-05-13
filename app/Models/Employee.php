<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'employee_code',
        'name',
        'email',
        'phone',
        'department_id',
        'designation',
        'joining_date',
        'reporting_manager_id',
        'building_id',
        'floor_id',
        'employment_status',
    ];

    protected $casts = [
        'joining_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reporting_manager_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(self::class, 'reporting_manager_id');
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function deskAllocations(): HasMany
    {
        return $this->hasMany(DeskAllocation::class);
    }

    public function assetAssignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function activeAssetAssignments(): HasMany
    {
        return $this->assetAssignments()->whereNull('returned_at');
    }

    public function hasActiveAssets(): bool
    {
        if ($this->relationLoaded('assetAssignments')) {
            return $this->assetAssignments->contains(fn (AssetAssignment $assignment) => $assignment->returned_at === null);
        }

        return $this->activeAssetAssignments()->exists();
    }

    public function onboardingRequests(): HasMany
    {
        return $this->hasMany(OnboardingRequest::class);
    }

    public function offboardingRequests(): HasMany
    {
        return $this->hasMany(OffboardingRequest::class);
    }
}

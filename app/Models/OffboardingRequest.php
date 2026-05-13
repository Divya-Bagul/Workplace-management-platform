<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffboardingRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'initiated_by',
        'last_working_day',
        'status',
        'assets_recovery_started_at',
        'assets_recovered_at',
        'desk_released_at',
        'completed_at',
        'hr_notes',
        'it_notes',
    ];

    protected $casts = [
        'last_working_day' => 'date',
        'assets_recovery_started_at' => 'datetime',
        'assets_recovered_at' => 'datetime',
        'desk_released_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}

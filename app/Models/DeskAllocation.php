<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeskAllocation extends Model
{
    protected $fillable = ['desk_id', 'employee_id', 'valid_from', 'valid_to', 'notes'];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function desk(): BelongsTo
    {
        return $this->belongsTo(Desk::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

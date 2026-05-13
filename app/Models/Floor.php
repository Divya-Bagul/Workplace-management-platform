<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floor extends Model
{
    protected $fillable = ['building_id', 'name', 'level'];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function desks(): HasMany
    {
        return $this->hasMany(Desk::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    protected $fillable = ['name', 'address'];

    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $fillable = ['asset_type_id', 'asset_tag', 'serial_number', 'status', 'notes'];

    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['region_id', 'name', 'block', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Label lengkap: "Ramba – Blok A – Kamar 101"
     * Hanya tampilkan komponen yang terisi.
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->region?->name,
            $this->block ?: null,
            $this->name,
        ]);

        return implode(' – ', $parts);
    }

    /**
     * Label singkat tanpa region: "Blok A – 101"
     */
    public function getShortNameAttribute(): string
    {
        $parts = array_filter([
            $this->block ?: null,
            $this->name,
        ]);

        return implode(' – ', $parts);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForRegion($query, $regionId)
    {
        return $query->where('region_id', $regionId);
    }
}

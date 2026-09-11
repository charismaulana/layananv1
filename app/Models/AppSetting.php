<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    /**
     * Get a setting value by key with optional fallback.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();
        return $setting ? ($setting->value ?? $default) : $default;
    }

    /**
     * Set or update a setting value.
     */
    public static function set(string $key, ?string $value, ?string $description = null): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            array_filter([
                'value'       => $value,
                'description' => $description,
            ], fn($v) => $v !== null)
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'label',
        'value',
        'type',
        'group',
        'description',
        'is_public',
        'sort_order'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Auto-cast value berdasarkan type
    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->castValue($value),
            set: fn ($value) => $this->prepareValue($value)
        );
    }

    // Cast value sesuai dengan type
    protected function castValue($value)
    {
        return match ($this->type) {
            'boolean' => (bool) $value,
            'number' => is_numeric($value) ? (float) $value : $value,
            'json' => json_decode($value, true),
            default => $value
        };
    }

    // Prepare value untuk disimpan
    protected function prepareValue($value)
    {
        return match ($this->type) {
            'boolean' => $value ? '1' : '0',
            'number' => (string) $value,
            'json' => json_encode($value),
            default => (string) $value
        };
    }

    // Scope untuk public settings
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    // Scope berdasarkan group
    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    // Static method untuk mendapatkan setting value
    public static function get($key, $default = null)
    {
        $cacheKey = "setting.{$key}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    // Static method untuk set setting value
    public static function set($key, $value)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        // Clear cache
        Cache::forget("setting.{$key}");

        return $setting;
    }

    // Get all public settings untuk landing page
    public static function getPublicSettings()
    {
        $cacheKey = 'settings.public';

        return Cache::remember($cacheKey, now()->addHours(12), function () {
            return static::public()
                ->orderBy('group')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('group');
        });
    }

    // Clear all settings cache
    public static function clearCache()
    {
        Cache::forget('settings.public');

        // Clear individual setting caches
        static::all()->each(function ($setting) {
            Cache::forget("setting.{$setting->key}");
        });
    }

    // Event hooks
    protected static function booted()
    {
        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }
}

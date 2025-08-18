<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
        'type' // string, integer, float, boolean, json
    ];

    protected $casts = [
        'value' => 'string'
    ];

    // Static method to get setting value
    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        // Cast value based on type
        return match($setting->type) {
            'integer' => (int) $setting->value,
            'float' => (float) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value
        };
    }

    // Static method to set setting value
    public static function setValue($key, $value, $description = null, $type = 'string')
    {
        // Convert value to string for storage
        $stringValue = match($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value
        };

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'description' => $description,
                'type' => $type
            ]
        );
    }

    // Get all settings as key-value pairs
    public static function getAllSettings()
    {
        return static::all()->mapWithKeys(function ($setting) {
            return [$setting->key => static::getValue($setting->key)];
        });
    }
}

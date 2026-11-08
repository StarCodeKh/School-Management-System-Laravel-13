<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /** Every known setting with its default value. Saved rows override these. */
    public const DEFAULTS = [
        // general (school_name falls back to APP_NAME, see allValues())
        'school_name' => null,
        'school_email' => null,
        'school_phone' => null,
        'school_address' => null,
        'currency_symbol' => '$',
        // invoice
        'invoice_due_days' => '14',
        'invoice_terms' => null,
        'invoice_notes' => null,
        // tax
        'tax_name' => 'Tax',
        'tax_rate' => '0',
        // bank
        'bank_account_holder' => null,
        'bank_name' => null,
        'bank_ifsc_code' => null,
        'bank_account_number' => null,
        // library
        'library_loan_days' => '14',
        'library_fine_per_day' => '0',
    ];

    private const CACHE_KEY = 'settings.all';

    /** All settings, defaults merged with saved values (cached until the next save). */
    public static function allValues(): array
    {
        $values = array_merge(self::DEFAULTS, Cache::rememberForever(
            self::CACHE_KEY,
            fn () => static::query()->pluck('value', 'key')->all()
        ));
        $values['school_name'] = $values['school_name'] ?: config('app.name');

        return $values;
    }

    public static function value(string $key, mixed $default = null): mixed
    {
        return self::allValues()[$key] ?? $default;
    }

    /** Save several settings at once; unknown keys are ignored. */
    public static function store(array $values): void
    {
        foreach (array_intersect_key($values, self::DEFAULTS) as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}

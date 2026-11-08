<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /** Read a school setting, e.g. setting('school_name'). */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::value($key, $default);
    }
}

if (! function_exists('money')) {
    /** Format an amount with the configured currency symbol, e.g. "$1,250.00". */
    function money(int|float|string|null $amount): string
    {
        return setting('currency_symbol', '$').number_format((float) $amount, 2);
    }
}

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Date Format
    |--------------------------------------------------------------------------
    |
    | The default date format used when converting DateTime objects to strings.
    |
    | You can customize this using any valid PHP date() format string.
    | Common options include:
    |
    | 'Y-m-d H:i:s' — 2025-06-19 14:30:00
    | 'Y-m-d'       — 2025-06-19
    | 'd/m/Y'       — 19/06/2025
    | 'm/d/Y'       — 06/19/2025
    | 'd M, Y'      — 19 Jun, 2025
    | 'D, d M Y H:i:s' — Thu, 19 Jun 2025 14:30:00
    |
    | Full PHP date format characters:
    | https://www.php.net/manual/en/datetime.format.php
    |
    */

    'date_format' => 'Y-m-d H:i:s',

    /*
    |--------------------------------------------------------------------------
    | Strict Type Validation
    |--------------------------------------------------------------------------
    |
    | If enabled, Dtomatic will throw an exception when a type mismatch occurs.
    |
    */

    'strict_types' => false,

    /*
    |--------------------------------------------------------------------------
    | Custom Value Converters
    |--------------------------------------------------------------------------
    |
    | You can register custom type converters here. When mapping a value,
    | Dtomatic will check if a converter exists for its type and use it.
    |
    */

    'custom_converters' => [
        // Example:
        // 'Carbon\Carbon' => \App\Converters\CarbonConverter::class,
    ],
];

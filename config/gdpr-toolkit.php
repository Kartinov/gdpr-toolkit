<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GDPR Toolkit Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can define default settings for your GDPR package.
    | These values can be published and overridden in the host app.
    |
    */

    // Column name heuristics for personal data
    'personal_data_columns' => [
        'email', 'phone', 'address', 'ip', 'dob', 'name',
    ],

    // Models to exclude from scanning
    'exclude_models' => [
        // App\Models\Example::class,
    ],

    // RoPA output format
    'ropa_output' => 'storage/app/gdpr-ropa.json',
];

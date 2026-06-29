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

    // Subject models (people we care about)
    'subject_models' => [
        'App\\Models\\User',
        // 'App\\Models\\Customer',
    ],

    // Personal fields to look for
    'personal_data_columns' => [
        'name', 'email', 'phone', 'address', 'dob', 'national_id',
    ],

    // Foreign key suffix convention (default "_id")
    'foreign_key_suffix' => '_id',

    // Explicit overrides for unusual foreign keys
    // e.g. reviewer_id should be treated as User
    'foreign_key_overrides' => [
        // 'reviewer_id' => 'App\\Models\\User',
        // 'manager_id'  => 'App\\Models\\Customer',
    ],

    // Models to exclude completely
    'exclude_models' => [],
];

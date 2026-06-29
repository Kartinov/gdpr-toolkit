# GDPR Toolkit for Laravel

[![Latest Stable Version](https://poser.pugx.org/kartinov/gdpr-toolkit/v/stable)](https://packagist.org/packages/kartinov/gdpr-toolkit)
[![Total Downloads](https://poser.pugx.org/kartinov/gdpr-toolkit/downloads)](https://packagist.org/packages/kartinov/gdpr-toolkit)
[![License](https://poser.pugx.org/kartinov/gdpr-toolkit/license)](https://packagist.org/packages/kartinov/gdpr-toolkit)

A lightweight package to help Laravel developers address GDPR requirements by scanning your database for personally identifiable information and generating a draft Record of Processing Activities (RoPA).

## Installation

Require the package via Composer:

```bash
composer require kartinov/gdpr-toolkit
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Kartinov\GdprToolkit\GdprToolkitServiceProvider"
```

## Configuration

Open the published `config/gdpr-toolkit.php` and adjust the settings to match your application.

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Subject Models
    |--------------------------------------------------------------------------
    |
    | Models representing individuals whose personal data you process.
    | The scan will inspect these models and any tables linked to them
    | via foreign key.
    |
    */

    'subject_models' => [
        App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Personal Data Columns
    |--------------------------------------------------------------------------
    |
    | Column names (or substrings) that indicate personally identifiable
    | information. The scan matches these against your database schema.
    |
    */

    'personal_data_columns' => [
        'name', 'email', 'phone', 'address', 'date_of_birth', 'national_id',
        'shipping_address', 'billing_address', 'ip_address', 'card_number',
    ],

    /*
    |--------------------------------------------------------------------------
    | Foreign Key Detection
    |--------------------------------------------------------------------------
    |
    | Convention for matching related tables to subject models.
    | e.g. "user_id" for User, "admin_id" for Admin.
    |
    */

    'foreign_key_suffix' => '_id',

    /*
    |--------------------------------------------------------------------------
    | Foreign Key Overrides
    |--------------------------------------------------------------------------
    |
    | Explicit mappings for foreign keys that do not follow the convention.
    | e.g. "reviewer_id" should be treated as a reference to User.
    |
    */

    'foreign_key_overrides' => [
        // 'reviewer_id' => App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Models
    |--------------------------------------------------------------------------
    |
    | Models and their related tables to skip entirely during scanning.
    |
    */

    'exclude_models' => [],
];
```

## Usage

### Scanning

Run the scan to generate a draft RoPA:

```bash
php artisan gdpr:scan
```

This inspects your Eloquent models and database tables, detects personally identifiable information via column-name heuristics and foreign key relationships, and writes the result to:

```
storage/app/gdpr-ropa.json
```

#### Output format

```json
{
    "generated_at": "2026-06-26T22:00:00+02:00",
    "total_tables_with_personal_data": 3,
    "data_map": {
        "User": {
            "fields": ["name", "email", "phone", "address"],
            "orders": ["shipping_address", "ip_address"]
        },
        "Admin": {
            "fields": ["name", "email"],
            "reviews": ["comment"]
        }
    }
}
```

Each subject model maps to:
- `fields` — personal data columns on the subject's own table
- `{related_table}` — personal data columns on tables linked by foreign key

### Exporting personal data (Article 15 / 20)

Generate a complete personal data export for a specific subject:

```bash
php artisan gdpr:export App\Models\User 1
php artisan gdpr:export App\Models\User john@example.com --output=storage/app/export.json
```

- First argument: fully qualified model class (e.g. `App\Models\User`)
- Second argument: identifier — ID or email
- `--output` (optional): custom output path, defaults to `storage/app/gdpr-export.json`

The export includes the subject's own personal fields plus all related table records linked by foreign key. An audit log entry is written to `audit_logs` if the table exists.

### Marking models with `HasPersonalData`

Attach the `HasPersonalData` trait to any Eloquent model and declare its personal data fields:

```php
use Kartinov\GdprToolkit\Traits\HasPersonalData;

class User extends Authenticatable
{
    use HasPersonalData;

    protected array $personalData = [
        'name',
        'email',
        'phone',
        'address',
    ];
}
```

The trait provides one method:

```php
$user->exportPersonalData(); // Returns array of field => value
```


## Important Disclaimer

This toolkit is a developer aid, not a legal service. The RoPA it generates is a technical draft — it does not guarantee GDPR compliance. Always have your records reviewed by a qualified legal professional before relying on them for regulatory purposes.

## License

This package is open-sourced software licensed under the MIT license.  
See the [LICENSE](LICENSE) file for details.

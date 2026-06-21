# GDPR Toolkit for Laravel

A lightweight package to help Laravel developers address GDPR requirements.

---

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

Adjust the published config file `config/gdpr-toolkit.php` to define which fields are considered personal data and which models should be excluded:

```php
return [
    'personal_data_columns' => ['email', 'phone', 'address'],
    'exclude_models' => [App\Models\User::class],
];
```

## Usage

Run the artisan command to generate a draft Record of Processing Activities (RoPA):

```bash
php artisan gdpr:scan
```

This will create a RoPA file in JSON format at:

```bash
storage/app/gdpr-ropa.json
```

The file lists each model and its personal‑data fields, giving you a developer‑friendly starting point for GDPR compliance.

## Documentation

For a deeper explanation of RoPA and how this package helps, see:

- [Record of Processing Activities (RoPA)](docs/ropa.md)

## License

This package is open-sourced software licensed under the MIT license.  
See the [LICENSE](LICENSE) file for details.

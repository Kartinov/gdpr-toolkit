# Record of Processing Activities (RoPA)

## What is RoPA?

RoPA stands for **Record of Processing Activities**, a requirement under GDPR Article 30. It is a structured inventory of what personal data your application processes, why it is processed, and how it is handled.

## Why developers need it

While compliance teams handle the legal side, developers are the ones who know where personal data actually lives in the codebase. This package helps by:

- **Visibility**: showing which models and fields contain personally identifiable information.
- **Compliance support**: providing a draft record that legal teams can refine.
- **Security awareness**: making it clear where sensitive data is stored and processed.

## How it works

Running `php artisan gdpr:scan` inspects your Eloquent models and database tables. It uses two signals to find personally identifiable information:

1. **Column-name heuristics** — matches configured `personal_data_columns` against actual database columns (e.g. `email`, `shipping_address`, `ip_address`).
2. **Foreign key relationships** — follows `{model}_id` columns (and configured overrides) to discover personal data stored in related tables.

### Example output

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

In this example:
- `User.fields` contains personal data stored directly on the `users` table.
- `User.orders` shows that the `orders` table is linked to `User` via `user_id` and holds `shipping_address` and `ip_address`.
- `Admin.reviews` shows that the `reviews` table is linked to `Admin` via `admin_id` and holds `comment`.

## How to use the output

1. Run `php artisan gdpr:scan` and open `storage/app/gdpr-ropa.json`.
2. Review the `data_map` and confirm that every flagged field is correct for your application.
3. Add any missing fields to `personal_data_columns` and re-scan.
4. Share the draft with your legal or compliance team to add purposes, retention periods, and recipients.

## Important note

This toolkit is designed for developers. The RoPA it generates is not a legal guarantee — it is a technical aid to help you identify personally identifiable information in your Laravel app.

Compliance teams should review and extend the draft RoPA with purposes, retention periods, and recipients before using it as an official record.

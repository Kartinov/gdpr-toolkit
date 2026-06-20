# Record of Processing Activities (RoPA)

## What is RoPA?
RoPA stands for **Record of Processing Activities**, a requirement under GDPR (Article 30).  
It’s essentially a structured inventory of what personal data your application processes, why it’s processed, and how it’s handled.

## Why developers need it
While compliance teams handle the legal side, developers are the ones who know where personal data actually lives in the codebase.  
RoPA helps developers by:
- **Visibility**: showing which models and fields contain personal data.
- **Compliance support**: providing a draft record that legal teams can refine.
- **Security awareness**: making it clear where sensitive data is stored and processed.

## How it’s used
With this package, you can run:

```bash
php artisan gdpr:scan
```

This command inspects your Eloquent models, flags fields that match personal‑data heuristics (like email, phone, ip_address), and generates a draft RoPA file in JSON format.
The file lists each model and its personal‑data fields, giving you a developer‑friendly starting point for GDPR compliance.

## Important note
This toolkit is designed for developers. The RoPA it generates is not a legal guarantee — it’s a technical aid to help you identify personal data in your Laravel app.
Compliance teams should review and extend the draft RoPA with purposes, retention periods, and recipients before using it as an official record.

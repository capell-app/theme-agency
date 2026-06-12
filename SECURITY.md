# Security Policy

## Supported Versions

Capell package security fixes target the active `4.x` line.

| Version | Supported |
| ------- | --------- |
| 4.x     | Yes       |

## Reporting a Vulnerability

Do not open a public GitHub issue for suspected vulnerabilities.

Email Capell security reports to [capell.app26@gmail.com](mailto:capell.app26@gmail.com) with:

- the affected package or route
- reproduction steps or proof of concept
- impact, including whether data, credentials, admin access, public output, or cached HTML is exposed
- suggested fix, if known

We will acknowledge reports as quickly as practical, triage impact, and coordinate fixes privately before public disclosure.

## Package Security Baseline

This repository enforces package-owned security contracts through:

- `capell.json` `security` metadata for every package
- `scripts/audit-package-security.php` for manifest, route, workflow, public Blade, and HTTP client checks
- `scripts/scan-secrets.php` for high-signal committed-secret patterns
- `COMPOSER=composer.local.json composer security:all` for dependency audits, secret scan, and security contract tests

For package work, use `docs/package-security-checklist.md` before release or broad package changes.

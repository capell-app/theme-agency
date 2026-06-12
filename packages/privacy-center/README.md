# Capell Privacy Center

Privacy Center gives Capell packages a shared compliance ledger for consent, policy acceptance, retention, privacy subject requests, exports, and anonymisation workflows.

## At A Glance

| Field            | Value                                                                                                                |
| ---------------- | -------------------------------------------------------------------------------------------------------------------- |
| Composer package | `capell-app/privacy-center`                                                                                          |
| Namespace        | `Capell\PrivacyCenter`                                                                                               |
| Product group    | Capell Compliance                                                                                                    |
| Surfaces         | Admin, frontend consent preferences, console                                                                         |
| Providers        | `Capell\PrivacyCenter\Providers\PrivacyCenterServiceProvider`, `Capell\PrivacyCenter\Providers\AdminServiceProvider` |
| Public routes    | `capell-privacy-center.consent.show`, `capell-privacy-center.consent.store`                                          |
| Command          | `privacy:apply-retention`                                                                                            |
| Hash secret      | `CAPELL_PRIVACY_CENTER_HASH_SECRET`                                                                                  |

## Why It Helps Your Capell Workflow

Owners get an auditable privacy ledger for cookie consent, policy acceptance, retention rules, and DSAR workflow state. Operators can review privacy requests, run retention rules, and verify compliance health from Capell.

Developers get Actions for consent, policy acceptance, privacy exports, retention execution, subject anonymisation, and request state transitions without coupling source packages to one table layout.

## What It Adds

- Filament resources for consent policies, consent records, policy acceptances, privacy requests, and retention rules.
- Privacy Center overview widget.
- Public cookie consent preference centre.
- Actions for registering policies, recording consent/acceptance, opening privacy requests, building exports, anonymising subjects, and applying retention rules.
- Daily scheduled `privacy:apply-retention` contribution.
- Health checks for tables, morph map aliases, and identity hash configuration.

## Boundaries

Privacy Center owns its own compliance ledger. It does not yet provide a public DSAR intake form or a cross-package subject-data export/erasure registry. Other packages still own their operational data until they contribute explicit privacy adapters.

Public consent output must not expose policy model IDs, hashed identifiers, package internals, admin URLs, editor state, or authoring markers. The package declares sensitive, non-cacheable frontend output because consent varies by subject.

## Runtime Surface

- Providers: `src/Providers/`
- Admin resources/widgets: `src/Filament/`
- Public controllers: `src/Http/Controllers/`
- Actions: `src/Actions/`
- Data objects: `src/Data/`
- Models: `src/Models/`
- Command: `src/Console/Commands/ApplyRetentionRulesCommand.php`
- Tests: `packages/privacy-center/tests`

## Docs

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Improvement plan](docs/improvement-plan.md)
- [Screenshots contract](docs/screenshots.json)

## Testing

```bash
vendor/bin/pest packages/privacy-center/tests --configuration=phpunit.xml
```

## Troubleshooting

| Symptom                                 | Likely cause                                                  | Check                                                                              | Fix                                                                              |
| --------------------------------------- | ------------------------------------------------------------- | ---------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| Consent recording fails                 | Hash secret/app key is missing or category payload is invalid | Check `CAPELL_PRIVACY_CENTER_HASH_SECRET`, `app.key`, and request category handles | Configure a hash secret and submit only known cookie categories                  |
| Retention command reports no work       | No active rules match due dates or models                     | In a host app, run `php artisan privacy:apply-retention --json`                    | Add or adjust active retention rules and rerun the command                       |
| DSAR status timestamps are inconsistent | Status was changed outside package Actions                    | Inspect `privacy_requests` audit timestamp fields                                  | Move workflow changes through verify/fulfil/reject Actions or admin page actions |

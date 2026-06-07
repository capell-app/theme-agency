# Capell Privacy Center

Privacy Center gives Capell packages a shared compliance ledger for consent, policy acceptance, retention, privacy subject requests, exports, and anonymization workflows.

## Included Capabilities

- Consent policy and policy acceptance records for versioned privacy, cookie, terms, and data-processing policies.
- Cookie-category consent decisions with hashed request evidence for IP address and user agent values.
- Privacy subject request records for access, export, deletion, correction, restriction, and objection workflows.
- Admin DSAR workflow actions for marking requests verified, fulfilled, or rejected while stamping the relevant audit timestamps.
- Retention rules for delete, anonymize, and review actions.
- `privacy:apply-retention` for manual or scheduled retention execution.
- Health diagnostics for required privacy tables, morph map aliases, and identity hash configuration.
- Five Filament admin resources for policies, consent records, policy acceptances, privacy requests, and retention rules.
- A Privacy Center overview widget with package-owned consent, request, and retention counts.
- A public cookie consent preference center that records granular category decisions through `RecordConsentAction`.

Privacy Center currently ships admin and console surfaces plus a public cookie consent preference center. It does not ship a public DSAR intake form or a cross-package subject-data export/erasure registry.

## Installation And Configuration

Install the package with the normal Capell package workflow for `capell-app/privacy-center`. The package requires `capell-app/core` and `capell-app/admin`, publishes the `capell-privacy-center` config, and registers five package-owned migrations:

- `privacy_consent_policies`
- `privacy_consent_records`
- `privacy_policy_acceptances`
- `privacy_retention_rules`
- `privacy_requests`

Configure `CAPELL_PRIVACY_CENTER_HASH_SECRET` in production. If that value is not set, Privacy Center falls back to Laravel's `app.key`; if neither value is configured, hashing consent evidence fails loudly instead of using a predictable default.

The config also exposes `privacy_request_due_days`, which defaults newly opened privacy requests to a 30-day due date when no explicit due date is supplied.

## Consent And Policy Records

Integrating packages should write Privacy Center records through Actions, not direct table writes:

- `RegisterConsentPolicyAction`
- `RecordConsentAction`
- `RecordPolicyAcceptanceAction`

`RecordConsentAction` stores cookie category, consent decision, jurisdiction, policy version, optional source model, optional subject model, and evidence metadata. Request IP address and user agent values are hashed before persistence. When an integration passes a source model that carries a loaded `subject` or `visit` relationship, Privacy Center mirrors that related model as the consent subject so package-owned exports and erasure can find the record.

The public preference center is available at `/privacy/consent`. It renders only category labels and explanatory copy, submits category handles, grants essential cookies automatically, and records optional categories as granted or denied without exposing policy model IDs, admin URLs, hashed identifiers, package internals, or editor state.

## Retention Execution

Run all active retention rules manually with:

```bash
privacy:apply-retention
```

Use `--json` to return a per-rule summary for automation. The package manifest advertises the same command as a daily scheduled job, and the service provider schedules it daily when the package is installed.

## Privacy Request Workflow

Operators should use the Privacy requests edit page actions to move DSAR records through verification, fulfilment, and rejection. The status and audit timestamp fields are read-only in the form so changes run through package Actions and preserve `verified_at`, `fulfilled_at`, `rejected_at`, and rejection reasons consistently.

`OpenPrivacyRequestAction` records access, export, delete, correction, restriction, and objection requests. `BuildPrivacyExportAction` exports Privacy Center's package-owned consent records, policy acceptances, and privacy requests for a subject while excluding internal IDs and hashed evidence values. `AnonymizePrivacySubjectAction` removes subject links and request evidence from Privacy Center's own ledger records.

Those export and erasure Actions do not yet collect or erase data from other packages such as Contacts, Newsletter, or Insights. Other packages must still own their operational data until a cross-package subject-data contribution registry ships.

## Admin Surfaces

The admin provider contributes these Filament surfaces when the package is installed:

- Consent policies: create, edit, and list versioned policy records.
- Consent records: list recorded consent decisions and policy versions.
- Policy acceptances: list accepted policy versions and contexts.
- Privacy requests: list and edit DSAR records, with workflow actions for verify, fulfil, and reject.
- Retention rules: create, edit, and list delete/anonymize/review rules.
- Overview widget: displays package-owned consent, request, and active retention counts.

## Audit And Safety Boundaries

Public output must not expose Privacy Center internals, package names, model identifiers, admin URLs, hashed identifiers, or editor state.

The package manifest declares `cacheable: false`, `sensitiveOutput: true`, and a 40ms frontend render budget because consent decisions vary by subject and request evidence is sensitive. The public preference center is deliberately cache-safe HTML plus a POST action; future public DSAR UI must preserve that boundary and prove anonymous/non-admin output does not expose authoring or privacy internals.

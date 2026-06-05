# Privacy Center

Privacy Center is the package-owned compliance foundation for Capell consent and privacy workflows.

It intentionally does not replace package-specific consent capture in packages such as Insights or Newsletter. Those packages can continue to record their own operational evidence and later call Privacy Center Actions when they need a shared compliance ledger.

## Foundation Scope

- Consent policies and version metadata.
- Cookie/category consent decisions with hashed request evidence.
- Policy version acceptance records.
- Privacy subject requests for access, export, deletion, correction, restriction, and objection workflows.
- Operator workflow actions for marking privacy requests verified, fulfilled, or rejected without bypassing audit timestamps.
- Filament admin resources for consent policies, consent records, policy acceptances, privacy requests, and retention rules.
- An admin overview widget for package-owned consent, request, and retention counts.
- Retention rules for package-owned or integration-owned data domains.
- Retention execution Actions and the `privacy:apply-retention` console command for delete, anonymize, and review workflows.
- Export and anonymization Actions that operate on Privacy Center records first.
- A daily retention schedule contribution so installers can discover and run the package-owned retention execution hook.

Privacy Center currently ships admin and console surfaces. It does not ship a public cookie banner, public DSAR intake form, public preference centre, or cross-package subject-data export/erasure registry.

## Integration Contract

Integrating packages should call Actions instead of writing Privacy Center tables directly:

- `RecordConsentAction`
- `RecordPolicyAcceptanceAction`
- `OpenPrivacyRequestAction`
- `CreateRetentionRuleAction`
- `ApplyRetentionRuleAction`
- `ApplyRetentionRulesAction`
- `BuildPrivacyExportAction`
- `AnonymizePrivacySubjectAction`

`RecordConsentAction` can infer a subject from a source model with a loaded `subject` or `visit` relation. That keeps mirrored records from integrations such as Insights discoverable by Privacy Center's package-owned export and anonymization Actions.

Public frontend output must not expose Privacy Center internals, package names, model identifiers, admin URLs, or editor state.

## Console

Run active retention rules manually with:

```bash
privacy:apply-retention
```

Use `--json` when automation needs the per-rule matched and affected record counts.

The service provider schedules the same command daily when the package is installed, matching the scheduled-job contribution in `capell.json`.

## Admin Request Workflow

Privacy request status changes should be handled from the edit-page workflow actions:

- Mark verified stamps `verified_at` and moves the request into processing.
- Mark fulfilled stamps `fulfilled_at`.
- Reject requires a rejection reason and stamps `rejected_at`.

The form keeps status, workflow timestamps, and rejection reason read-only so operators cannot bypass the Actions that maintain the compliance audit trail.

## Export And Erasure Boundaries

`BuildPrivacyExportAction` exports Privacy Center's own consent records, policy acceptances, and privacy requests for a subject. It removes internal primary keys, subject/source links, IP hashes, user-agent hashes, and email hashes from the exported rows.

`AnonymizePrivacySubjectAction` removes subject links and hashed/request evidence from Privacy Center consent records, policy acceptances, and privacy requests. It does not erase package-owned data from Contacts, Newsletter, Insights, or other integrations. Those packages must continue to own their operational deletion/export behavior until a dedicated cross-package subject-data contribution contract ships.

## Audit And Safety Boundaries

The package stores hashed request evidence for consent and policy acceptance workflows. `CAPELL_PRIVACY_CENTER_HASH_SECRET` should be set explicitly in production; if no package hash secret and no `app.key` are available, hashing fails instead of falling back to a predictable salt.

The manifest marks the package as non-cacheable with sensitive output and `frontendRenderBudgetMs: 0` because there is no public frontend render surface today. Future public DSAR or cookie-consent UI must keep Privacy Center internals, package names, model identifiers, admin URLs, hashed identifiers, and editor state out of anonymous and non-admin output.

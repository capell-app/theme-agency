# Privacy Center

Privacy Center is the package-owned compliance foundation for Capell consent and privacy workflows.

It intentionally does not replace package-specific consent capture in packages such as Insights or Newsletter. Those packages can continue to record their own operational evidence and later call Privacy Center Actions when they need a shared compliance ledger.

## Foundation Scope

- Consent policies and version metadata.
- Cookie/category consent decisions with hashed request evidence.
- Policy version acceptance records.
- Privacy subject requests for access, export, deletion, correction, restriction, and objection workflows.
- Operator workflow actions for marking privacy requests verified, fulfilled, or rejected without bypassing audit timestamps.
- Retention rules for package-owned or integration-owned data domains.
- Retention execution Actions and the `privacy:apply-retention` console command for delete, anonymize, and review workflows.
- Export and anonymization Actions that operate on Privacy Center records first.
- A daily retention schedule contribution so installers can discover and run the package-owned retention execution hook.

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

Public frontend output must not expose Privacy Center internals, package names, model identifiers, admin URLs, or editor state.

## Console

Run active retention rules manually with:

```bash
privacy:apply-retention
```

Use `--json` when automation needs the per-rule matched and affected record counts.

## Admin Request Workflow

Privacy request status changes should be handled from the edit-page workflow actions:

- Mark verified stamps `verified_at` and moves the request into processing.
- Mark fulfilled stamps `fulfilled_at`.
- Reject requires a rejection reason and stamps `rejected_at`.

The form keeps status, workflow timestamps, and rejection reason read-only so operators cannot bypass the Actions that maintain the compliance audit trail.

## Remaining Admin Surfaces

The current package owns the compliance records and workflows. Admin resources and dashboard widgets remain as explicit manifest deferrals until there is a fuller operator UI for policy review, request queues, and retention health.

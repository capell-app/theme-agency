# Privacy Center

Privacy Center is the package-owned compliance foundation for Capell consent and privacy workflows.

It intentionally does not replace package-specific consent capture in packages such as Insights or Newsletter. Those packages can continue to record their own operational evidence and later call Privacy Center Actions when they need a shared compliance ledger.

## Foundation Scope

- Consent policies and version metadata.
- Cookie/category consent decisions with hashed request evidence.
- Policy version acceptance records.
- Privacy subject requests for access, export, deletion, correction, restriction, and objection workflows.
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

## Remaining Admin Surfaces

The current package owns the compliance records and workflows. Admin resources and dashboard widgets remain as explicit manifest deferrals until there is an operator UI for policy review, request queues, and retention health.

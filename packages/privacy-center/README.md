# Capell Privacy Center

Privacy Center gives Capell packages a shared compliance ledger for consent, policy acceptance, retention, privacy subject requests, exports, and anonymization workflows.

## Included Capabilities

- Consent policy and policy acceptance records.
- Cookie-category consent decisions with hashed request evidence.
- Privacy subject request records for access, export, deletion, correction, restriction, and objection workflows.
- Admin DSAR workflow actions for marking requests verified, fulfilled, or rejected while stamping the relevant audit timestamps.
- Retention rules for delete, anonymize, and review actions.
- `privacy:apply-retention` for manual or scheduled retention execution.
- Health diagnostics for required privacy tables, morph map aliases, and identity hash configuration.

## Retention Execution

Run all active retention rules manually with:

```bash
privacy:apply-retention
```

Use `--json` to return a per-rule summary for automation. The package manifest advertises the same command as a daily scheduled job.

## Privacy Request Workflow

Operators should use the Privacy requests edit page actions to move DSAR records through verification, fulfilment, and rejection. The status and audit timestamp fields are read-only in the form so changes run through package Actions and preserve `verified_at`, `fulfilled_at`, `rejected_at`, and rejection reasons consistently.

Public output must not expose Privacy Center internals, package names, model identifiers, admin URLs, hashed identifiers, or editor state.

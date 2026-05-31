# Contacts Foundation Plan

This package now owns the central CRM records that other Capell packages can map into without coupling to each other.

## Implemented slice

- Package metadata, manifest, config, translations, and runtime provider.
- Migrations for contacts, organisations, contact-organisation memberships, leads, and activities.
- Eloquent models with encrypted personal/profile fields, site scoping, typed enum casts, relationships, morph sources, and protected-table registration.
- `ContactIdentityData` and `ContactActivityData` as structured action boundaries.
- `FindOrCreateContactAction` for initial identity capture and deduplication by email, phone, or source model.
- `RecordContactActivityAction` for appending package-originated timeline records.

## Next slices

1. Admin resources: contact, organisation, lead, and activity list/detail surfaces with translated labels and policy coverage.
2. Source adapters: package-local integration actions for Form Builder, Newsletter, Comments, Access Gate, Events, Shopify Commerce, and Campaign Studio.
3. Merge and deduplication workflow: confidence scoring, audit records, reversible merges, and source priority rules.
4. Consent and privacy tooling: export, erase, suppress, and do-not-contact state that downstream marketing packages can honor.
5. Attribution rollups: activity summaries and conversion/source counts for dashboards without querying package-specific internals.
6. Installation health checks: table/index checks and source-adapter readiness checks once integrations exist.

## Boundary notes

- This package should expose actions and contracts to other packages; other packages should not reach into its internals.
- Public frontend output should not include contact IDs, lead IDs, internal source metadata, or admin URLs.
- Source packages should pass portable identity/activity data into actions and keep their own package-specific payloads minimal.

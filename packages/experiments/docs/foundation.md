# Experiments Package Foundation

This package is the package-local foundation for Capell experiments, A/B testing, personalization, and winner reporting.

## Scope

- Experiment aggregates store site, status, subject type, optional polymorphic subject reference, schedule, traffic percentage, and winner fields.
- Variants store weighted allocation data and a JSON payload for package-owned render instructions.
- Audience rules evaluate request/context data without importing Campaign Studio, Insights, HTML Cache, or Frontend Optimizer classes.
- Allocations are sticky by `allocation_key` and are also indexed by a SHA-256 hash for stable lookups.
- Request context variant resolution finds active, site/subject-aware experiments, delegates sticky allocation, and returns render-safe variant payload plus cache variation metadata.
- Goals and goal events record conversion intent and variant-level outcomes.
- Winner reports summarize allocation count, conversion count, and conversion rate.
- `DeclareExperimentWinnerAction` persists the selected winning variant, declaration timestamp, and report snapshot for admin/Campaign Studio consumption. Statistical confidence is intentionally a later slice.
- Filament admin resources let operators manage experiments, variants, goals, and audience rules without reaching directly into database tables.

## Integration Points

Future packages should integrate through explicit boundaries:

- Campaign Studio can create experiments using `subject_type=campaign`, `subject_class`, and `subject_id`.
- Page experiments can use `subject_type=page` with a page model reference.
- Insights can call `AllocateVariantAction` with an `ExperimentContextData` source/external ID and later call `RecordGoalEventAction`.
- HTML Cache and Frontend Optimizer should vary cache/profile keys by the resolved variant key; this package does not currently mutate public output or caches.

## Deliberate deferrals

- Allocation and goal event reporting can be promoted into dedicated read-only admin resources once operators need drill-down beyond the experiment table counts and winner report action.
- Frontend components for page/campaign variant rendering remain package-specific integration work so this package does not leak experiment metadata into public HTML by default.

## Public Output Safety

This foundation does not inject Blade, JavaScript, editor markers, signed URLs, model IDs, or admin metadata into public responses. Allocation and reporting are stored server side and exposed only through package Actions/Data.

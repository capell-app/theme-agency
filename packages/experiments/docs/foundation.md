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
- `DeclareExperimentWinnerAction` persists the selected winning variant, declaration timestamp, and report snapshot for admin/Campaign Studio consumption.
- Filament admin resources let operators manage experiments, variants, goals, and audience rules without reaching directly into database tables.

## Integration Points

Packages integrate through explicit boundaries:

- Campaign Studio can create experiments using `subject_type=campaign`, `subject_class`, and `subject_id`.
- Page experiments can use `subject_type=page` with a page model reference.
- Insights can call `AllocateVariantAction` with an `ExperimentContextData` source/external ID and later call `RecordGoalEventAction`.
- HTML Cache and Frontend Optimizer can vary cache/profile keys by the resolved variant key from `ResolvedExperimentVariantData`.

## Rendering Contract

Experiments returns render-safe variant payloads and cache variation metadata through Actions/Data. Owning page, campaign, theme, HTML Cache, or Frontend Optimizer integrations consume those payloads without this package injecting Blade, JavaScript, editor markers, signed URLs, model IDs, or admin metadata into public responses.

## Public Output Safety

This foundation does not inject Blade, JavaScript, editor markers, signed URLs, model IDs, or admin metadata into public responses. Allocation and reporting are stored server side and exposed only through package Actions/Data.

# Capell Experiments

Server-side A/B testing for Capell pages, campaigns, and package-owned growth workflows.

Experiments lets operators create experiments, variants, goals, and audience rules in the Capell admin. Runtime integrations call package Actions to resolve a visitor's variant, render the consuming package's chosen payload, and record goal events without adding client-side testing scripts or public authoring markers.

## What It Ships

- Filament resources for experiments, variants, goals, and audience rules.
- Deterministic weighted allocation with sticky or per-request allocation strategies.
- Audience rules for path, query, UTM, referrer, attributes, and segments.
- Goal event recording and statistically gated winner reports.
- Cache-safety metadata for frontend integrations that resolve variants during render.
- Health diagnostics for required tables and model storage.

## Resolve A Variant

Use `ResolveExperimentVariantForContextAction` from the consuming page, campaign, theme, or render integration. Pass a stable visitor key and enough context to match the intended experiments.

```php
use Capell\Experiments\Actions\ResolveExperimentVariantForContextAction;
use Capell\Experiments\Data\ExperimentContextData;
use Capell\Experiments\Enums\ExperimentSubjectType;

$resolved = ResolveExperimentVariantForContextAction::run(
    allocationKey: $visitorHash,
    context: new ExperimentContextData(
        siteId: $site->id,
        subjectType: ExperimentSubjectType::Page->value,
        subjectClass: $page::class,
        subjectId: $page->id,
        path: request()->path(),
        query: request()->query(),
        utm: request()->only(['utm_source', 'utm_medium', 'utm_campaign']),
        segments: $knownSegments,
    ),
);

if ($resolved !== null) {
    $variantKey = $resolved->variantKey;
    $payload = $resolved->variantPayload;
}
```

The resolver records a frontend render contribution when a variant is selected, marking the response as not safe for shared static HTML caching and adding experiment/variant vary metadata for cache-aware packages.

## Record A Goal Event

Goal events are recorded against an explicit allocation and goal. Store or pass the allocation id returned by the allocation data when your integration needs to connect a later conversion to the resolved variant.

```php
use Capell\Experiments\Actions\RecordGoalEventAction;
use Capell\Experiments\Data\ExperimentGoalEventData;

RecordGoalEventAction::run(
    allocation: $allocation,
    goal: $goal,
    data: new ExperimentGoalEventData(
        eventKey: 'demo-requested',
        valueAmount: '99.00',
        metadata: [
            'source' => 'form-builder',
        ],
    ),
);
```

## Public Output Boundary

Experiments is currently an admin package plus runtime Action surface. It does not inject Blade, JavaScript, signed editor URLs, model ids, or package internals into public HTML. Consumer packages own rendering and goal-beacon routes until a first-party frontend surface is deliberately shipped.

## Integration Notes

- Campaign Studio can create campaign-scoped experiments using `subject_type=campaign`, `subject_class`, and `subject_id`.
- Form Builder can record form-submission goals after resolving the allocation used by the rendered page or campaign.
- Insights can consume allocation and goal metadata for reporting once an integration is added.
- HTML Cache and Frontend Optimizer should respect resolver cache-safety contributions when variants are resolved during render.

## Verify

Run focused package checks from this repository when tests are allowed:

```bash
vendor/bin/pest packages/experiments/tests --configuration=phpunit.xml
```

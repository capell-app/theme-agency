# Experiments Overview

Status: **Available, schema-owning** · Kind: **package** · Tier: **premium** · Bundle: **growth** · Contexts: **admin** · Product group: **Capell Growth**

Experiments gives Capell server-side A/B testing foundations: admin-managed experiments, weighted variants, audience rules, goal events, and winner reports.

## Non-Technical Overview

Use Experiments when a team wants to test page, campaign, or package-owned growth changes without loading third-party testing scripts into the public site. Operators configure tests in admin, choose audience rules, compare variants, and review goal results.

The package does not render public widgets by itself. A consuming page, theme, campaign, or package resolves a variant and renders the chosen payload through its own safe frontend surface.

## What This Package Adds

- Filament resources for experiments, variants, goals, and audience rules.
- Weighted allocation with sticky or per-request strategies.
- Audience matching for path, URL, referrer, query, UTM, attributes, and segments.
- Goal event recording tied to an experiment allocation.
- Winner report and declaration actions.
- Frontend render contribution metadata so cache-aware packages know variant output is visitor-varying.

## Developer Deep Dive

Use `ResolveExperimentVariantForContextAction` in the package that owns rendering. Pass a stable allocation key and enough `ExperimentContextData` for the resolver to find a matching active experiment.

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
        segments: $segments,
    ),
);

$payload = $resolved?->variantPayload;
```

Record a conversion only when the integration can identify the allocation and goal:

```php
use Capell\Experiments\Actions\RecordGoalEventAction;
use Capell\Experiments\Data\ExperimentGoalEventData;

RecordGoalEventAction::run(
    allocation: $allocation,
    goal: $goal,
    data: new ExperimentGoalEventData(
        eventKey: 'demo-requested',
        valueAmount: '99.00',
        metadata: ['source' => 'form-builder'],
    ),
);
```

## Boundaries

- Experiments owns experiment records, variant allocation, audience evaluation, goal events, and reporting.
- Consuming packages own public rendering and any goal-beacon routes.
- Shared static HTML caches must not serve one visitor's variant to another visitor. Respect the resolver's cache contribution metadata.
- Public output should not expose experiment internals, admin URLs, authoring metadata, or package implementation details.

## Screenshot Plan

`docs/screenshots.json` covers experiments, variants, goals, and audience rules admin indexes.

## Verification

```bash
vendor/bin/pest packages/experiments/tests --configuration=phpunit.xml
```

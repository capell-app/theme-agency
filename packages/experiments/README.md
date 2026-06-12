# Capell Experiments

Experiments adds server-side A/B testing for Capell pages, campaigns, and package-owned growth workflows.

## At A Glance

| Field            | Value                                                                   |
| ---------------- | ----------------------------------------------------------------------- |
| Composer package | `capell-app/experiments`                                                |
| Namespace        | `Capell\Experiments`                                                    |
| Product group    | Capell Growth, premium growth bundle                                    |
| Surfaces         | Admin, runtime Action surface                                           |
| Provider         | `Capell\Experiments\Providers\ExperimentsServiceProvider`               |
| Admin resources  | Experiments, variants, goals, audience rules                            |
| Supports         | Campaign Studio, Frontend Optimizer, HTML Cache, Form Builder, Insights |
| Command          | `experiments:sync-statuses` where installed                             |

## Why It Helps Your Capell Workflow

Owners can test page and campaign variants without embedding third-party client scripts. Operators manage experiments, variants, audience rules, goals, and winner reporting in Capell admin.

Developers resolve variants and record goals through Actions. Consumer packages keep rendering ownership while Experiments contributes allocation, cache-safety metadata, and reporting inputs.

## What It Adds

- Filament resources for experiments, experiment variants, experiment goals, and audience rules.
- Weighted allocation with sticky and per-request strategies.
- Audience rules for path, query, UTM, referrer, attributes, and segments.
- Goal event recording and statistically gated winner reports.
- Winner declaration and status sync Actions.
- Cache variation metadata for frontend integrations that resolve variants during render.

## Boundaries

Experiments is currently an admin package plus runtime Action surface. It does not inject Blade, JavaScript, signed editor URLs, model IDs, package names, or authoring metadata into public HTML.

Consumer packages own rendering and conversion routes. HTML Cache and Frontend Optimizer integrations should respect the resolver cache-safety contribution when a variant is selected during render.

## Runtime Surface

- Provider: `src/Providers/ExperimentsServiceProvider.php`
- Admin resources: `src/Filament/Resources/`
- Actions: `src/Actions/`
- Data objects: `src/Data/`
- Models: `src/Models/`
- Enums: `src/Enums/`
- Command: `src/Console/Commands/SyncExperimentStatusesCommand.php`
- Tests: `packages/experiments/tests`

## Docs

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Foundation](docs/foundation.md)
- [Improvement plan](docs/improvement-plan.md)
- [Screenshots contract](docs/screenshots.json)

## Testing

```bash
vendor/bin/pest packages/experiments/tests --configuration=phpunit.xml
```

## Troubleshooting

| Symptom                               | Likely cause                                                                            | Check                                                                                    | Fix                                                               |
| ------------------------------------- | --------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------- | ----------------------------------------------------------------- |
| No variant resolves                   | Experiment is inactive, dates/status exclude it, or audience rules do not match context | Check experiment status, dates, subject fields, and `EvaluateAudienceRulesAction` inputs | Activate the experiment and pass complete `ExperimentContextData` |
| Allocations change unexpectedly       | Per-request strategy is configured or allocation key is unstable                        | Inspect the experiment allocation strategy and visitor key                               | Use sticky allocation with a stable non-PII allocation key        |
| Shared cache serves the wrong variant | Consuming renderer ignored cache-safety metadata                                        | Check frontend cache vary metadata after `ResolveExperimentVariantForContextAction`      | Vary/bypass shared cache when variants resolve during render      |

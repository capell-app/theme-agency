# AI Creator

AI Creator is a **Draft** first-party Capell package in the **Capell Commercial** product group. It ships as `capell-app/ai-creator` and adds reviewed AI-assisted creation sessions for normal Capell content work.

## At a glance

- Composer package: `capell-app/ai-creator`
- Namespace: `Capell\AiCreator`
- Runtime surfaces: admin, Agent Bridge
- Service provider: `Capell\AiCreator\Providers\AiCreatorServiceProvider`
- Required packages: `capell-app/admin`, `capell-app/agent-bridge`, `capell-app/ai-orchestrator`, `capell-app/core`

## What it adds

AI Creator starts from an existing Capell site or workspace and creates a reviewed session for content, pages, layouts, reusable sections, structured records, navigation suggestions, SEO drafts, and optional site/theme setup. The first implementation slice persists creator sessions, computes deterministic extension recommendations, exposes Agent Bridge capabilities, and keeps apply behavior behind confirmation.

AI output is planning input only. Deterministic Actions own persistence and apply state, and no raw AI-generated PHP or Blade is executed directly.

## Works better with

AI Creator recommends extensions during planning and preview:

- `capell-app/layout-builder` for complex page composition, reusable layouts, widgets, and scoped widget assets.
- `capell-app/content-sections` for reusable service blocks, testimonials, FAQs, feature rows, and landing sections.
- `capell-app/structured-content-library` for services, locations, team members, case studies, resources, and other reusable business records.
- `capell-app/media-library` and `capell-app/media-ai` for managed assets, alt text, metadata, and media cleanup.
- `capell-app/seo-suite`, `capell-app/insights`, `capell-app/ga4-reports`, and `capell-app/site-monitor` for search readiness and post-launch visibility.
- `capell-app/publishing-studio` for approvals, scheduling, release workspaces, and controlled publishing.
- `capell-app/navigation`, `capell-app/search`, `capell-app/blog`, `capell-app/form-builder`, `capell-app/newsletter`, `capell-app/events`, and `capell-app/campaign-studio` when the requested content needs those workflows.

AI Creator does not silently install optional packages. Recommendations are marked required, recommended, or optional so admins can decide what to install before applying a plan.

## Code map

- `src/Actions`: deterministic session and recommendation operations.
- `src/AgentBridge`: Agent Bridge capability provider and capability actions.
- `src/Data`: structured input/output payloads.
- `src/Enums`: session and recommendation states.
- `src/Models`: package-owned persisted creator sessions.
- `database/migrations`: `capell_ai_creator_sessions`.
- `resources/lang/en`: user-facing strings.
- `tests`: package-local Pest coverage.

## Safety

Public output must not expose authoring metadata, model IDs, signed URLs, field paths, selectors, package internals, or editor controls. The package stores plans and recommendations in admin-owned session state; public rendering remains owned by the target packages and deterministic Capell Actions.

## Verification

```bash
vendor/bin/pest packages/ai-creator/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```

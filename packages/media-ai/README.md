# Media AI

<!-- prettier-ignore-start -->

## What This Plugin Adds

Media AI is an **Available**, **No schema impact** Capell package in the **Capell Media** product group. It ships as `capell-app/media-ai` and extends these surfaces: admin, console.

Media AI adds a provider-backed "Doctor image" action to image records in the Capell Admin media library, letting editors improve images, remove backgrounds or objects, restore damaged assets, and request upscales without leaving the CMS. It ships the `ImageDoctor` contract and a safe null implementation rather than a production AI provider; bind an implementation directly or through AI Orchestrator, and the action stays hidden until one is configured. Media AI is positioned as a premium Capell Media add-on to the free Media Library workflow, with no public frontend output and no database writes of its own.

After install, admins get package-owned management or reporting surfaces inside Capell.

Status details:

- Status: Available
- Tier: premium
- Bundle: media
- Composer package: `capell-app/media-ai`
- Namespace: `Capell\MediaAI`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, and Filament classes instead of pushing this behaviour into core or application code.

**For teams:** Provider-backed image editing inside Capell's media library: improve images, remove backgrounds or objects, restore, and upscale through a Doctor image action that stays hidden until configured.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Doctor image action on the Media edit page (admin, required).

## Technical Shape

- Service providers: `Capell\MediaAI\Providers\MediaAIServiceProvider`.
- Config files: `packages/media-ai/config/capell-media-ai.php`.
- Filament classes: `MediaAIEditActionExtender`.
- Actions: `ApplyImageDoctorMetadataAction`, `QueueBatchImageDoctorRequestsAction`.
- Data objects: `ImageDoctorRequest`, `ImageDoctorResult`.
- Jobs: `RunImageDoctorJob`.
- Command signatures: `media-ai:doctor-batch`.
- Console command classes: `QueueImageDoctorBatchCommand`.
- Health checks: `Capell\MediaAI\Health\MediaAIHealthCheck`.

## Data Model

This package has no schema impact. It does not declare package-owned migrations or required tables.

Docs gap: document extension points here if the package delegates persistence to a host package.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: review package jobs or schedules before install.
- Cache tags: none declared.
- Commands: `media-ai:doctor-batch`.

## Common Pitfalls

- Run package commands from the host app; in this repository use `vendor/bin/pest` for package tests.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |

## Quick Start

1. Install the package: `composer require capell-app/media-ai`.
2. Run the required setup: no package migrations are declared; clear cached config and routes if the host app uses caches.
3. Open the related Capell admin surface and verify Media AI appears.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Ai Orchestrator](../ai-orchestrator/README.md), [Media Library](../media-library/README.md), [Seo Suite](../seo-suite/README.md).
- Focused tests: `vendor/bin/pest packages/media-ai/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->

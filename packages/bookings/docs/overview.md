# Bookings Technical Overview

<!-- prettier-ignore-start -->

Bookings is a Capell Operations package for request-led appointment and lesson workflows. It is designed to stay useful at three sizes:

1. a simple public request form with an admin queue;
2. a daily operations surface for reminders, reviews, waitlists, changes, and travel;
3. a richer package-backed workflow with payments, reporting, retention, and AI-assisted prompts.

The package lives inside Laravel as `capell-app/bookings`. It contributes Capell admin resources, routes, migrations, scheduled jobs, settings, health checks, and Actions. It does not take over the public frontend.

## Reader Fit

| Reader | What to look at first |
| --- | --- |
| Site owner | The package turns appointment demand into a managed workflow rather than scattered forms, emails, and spreadsheets. |
| Admin/operator | Start in appointment requests, message logs, review requests, waitlist, and day planner. |
| Laravel developer | Review Actions, tokenized public routes, migrations, provider registration, and package tests. |
| Agency | Use the same starting shape for each client, then enable travel, waitlist, payment, or reporting only where useful. |
| Evaluator | This is not a hosted booking SaaS; it is booking capability installed into a Capell/Laravel site. |

## Workflow Layers

| Layer | Surfaces | Notes |
| --- | --- | --- |
| Request | Public `/bookings`, services, staff, locations, availability windows/exceptions | The minimum useful install. |
| Queue | Appointment request resource, audit logs, confirmations, cancellations | Keeps operators out of one-off email workflows. |
| Communication | Reminders, message logs, consent, portal lesson links, change proposals | Customer-specific links are temporary and opaque. |
| Operations | Day planner, travel observations, work zones, provisional holds, payment gates, group sessions, waitlists | Enable progressively; do not make small teams configure everything. |
| Improvement | Reviews, participant loops, lesson skills, bundles, fuel reports, service-area heatmap, retention pruning, owner prompts | Adds feedback and management signals once volume justifies it. |

## Public Boundary

Public customer routes are deliberately narrow:

- booking request submissions are throttled;
- staff calendar feeds use opaque tokens;
- portal lesson/consent links use temporary signatures and encrypted payloads;
- proposal, review, and review participant links use temporary signatures plus hashed tokens;
- webhook ingestion is throttled and authenticated with a configured header or bearer token.

Public Blade must stay cache-safe and visitor-safe: no authoring surface, model IDs, field paths, permissions, signed editor URLs, package internals, or database queries.

## Admin Surface

The package contributes resources for:

- setup: services, staff, locations, availability, exceptions, lesson series;
- daily operations: appointment requests, day planner, message logs, group sessions, waitlist;
- quality and follow-up: review requests, review participants, change proposals, owner prompts;
- geography and travel: observations, work zones, fuel and area reporting.

Keep business operations in Actions. Filament resources should remain thin setup, list, detail, and operator surfaces.

## Extension And Integration Points

Bookings supports adjacent Capell packages without requiring them:

- Customer Portal for lesson history and self-service links.
- Payments for paid holds and payment-gated confirmation.
- Notifications for production message delivery.
- AI Orchestrator for owner prompts and schedule suggestions.
- Media Library for lesson photos.
- SEO Suite and Events where public booking pages or clinics need richer site integration.

Webhook ingestion is intentionally generic: providers post to the Bookings route with a configured provider token, and domain-specific processors can consume recorded events.

## Verification Expectations

Run focused package checks after changes:

```bash
vendor/bin/pest packages/bookings/tests --configuration=phpunit.xml
vendor/bin/phpstan analyse packages/bookings/src packages/bookings/tests --configuration=phpstan.neon --memory-limit=4G --no-progress
```

For browser-visible changes, verify a consuming site route such as Equi Dynamics `/bookings` at desktop and mobile widths.

<!-- prettier-ignore-end -->

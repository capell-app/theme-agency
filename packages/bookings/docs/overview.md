# Bookings

<!-- prettier-ignore-start -->

## What This Plugin Adds

Bookings is an **Available**, **Schema-owning** Capell plugin in the **Capell Operations** product group. It ships as `capell-app/bookings` and extends admin, console, and frontend surfaces.

The package provides adaptive lesson booking workflows: booking requests, availability, confirmations, reminders, lesson series, portal lesson records, consent, travel-aware scheduling, provisional holds, payment gates, group clinics, review loops, waitlists, webhook ingestion, skill progress, bundles, cancellation prompts, reporting, import, risk scoring, owner digests, GDPR export/erasure, and retention pruning.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Public booking request form.
- Appointment request admin queue.
- Review request detail with participant responses.
- Waitlist admin queue.

## Technical Shape

- Service provider: `Capell\Bookings\Providers\BookingsServiceProvider`.
- Config file: `packages/bookings/config/capell-bookings.php`.
- Route file: `packages/bookings/routes/web.php`.
- Admin resources: setup resources, appointment queue, day planner, group sessions, message logs, review requests, travel observations, work zones, owner prompts, change proposals, and waitlist entries.
- Public routes: request form, staff calendar feeds, opaque portal lesson/consent links, tokenized proposals, tokenized reviews, tokenized participant reviews, and configured webhook ingestion.
- Console commands: due reminders, workflow expiry, review scheduling, and retention pruning.
- Manifest contributions: admin resources, models, routes, and scheduled jobs.
- Health check: `Capell\Bookings\Health\BookingsHealthCheck`.

## Security And Public Boundary

Generated customer links use temporary signatures. Portal links carry encrypted opaque payloads. Change proposal, review request, and participant review links resolve through hashed tokens. Public customer URLs do not expose internal model IDs.

Public Blade views are intentionally plain customer surfaces. Do not add authoring metadata, admin URLs, model IDs, package internals, or database queries to these views.

## Data Model

Required tables cover booking setup, appointment requests, audit logs, lesson notes, messaging consent/logs, travel, work zones, change proposals, group sessions, review requests/participants, owner prompts, webhook events, waitlist entries, lesson skills, and bundles.

Retention-sensitive surfaces use package settings and pruning actions. Token fields are hashed at rest.

## Install Impact

- Admin navigation adds Bookings resources.
- Permissions cover setup CRUD, appointment updates, planner/message/review/travel visibility, work zone CRUD, owner prompt updates, change proposal updates, and waitlist CRUD.
- Public request submissions and webhooks are throttled.
- Webhook ingestion requires configured provider tokens.
- Scheduled jobs run reminders, workflow expiry, review scheduling, and retention pruning.

## Common Pitfalls

- Use Actions to generate portal, consent, proposal, and review URLs.
- Configure webhook tokens before exposing provider endpoints.
- Keep public Blade output anonymous-safe and cache-safe.
- Keep manifest permissions, contributions, docs, screenshots, and tests aligned with shipped resources.

## Quick Start

1. Install the package: `composer require capell-app/bookings`.
2. Run host package setup/migrations.
3. Configure settings, availability, services, staff, webhook tokens if used, and message channels.
4. Verify `/bookings`, admin appointment requests, review requests, waitlist, and message logs.

<!-- prettier-ignore-end -->

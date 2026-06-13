# Bookings

<!-- prettier-ignore-start -->

## What This Plugin Adds

Bookings is an **Available**, **Schema-owning** Capell plugin in the **Capell Operations** product group. It ships as `capell-app/bookings` and extends admin, console, and frontend surfaces.

Bookings now covers the full adaptive lessons workflow: public booking requests, services, staff, locations, availability, appointment confirmations, reminders, lesson series, travel-aware planning, provisional holds, payments, group clinics, review loops, waitlists, webhook ingestion, lesson progress, bundles, cancellation support, reporting, retention, and AI-assisted owner prompts.

Status details:

- Status: Available
- Tier: premium
- Bundle: operations
- Composer package: `capell-app/bookings`
- Namespace: `Capell\Bookings`
- Theme key: not applicable

## Why It Matters

**For developers:** The package keeps booking domain behaviour inside package-owned Actions, models, migrations, Filament resources, routes, settings, and Blade views instead of pushing it into Capell core or host applications.

**For teams:** Bookings gives operators one place to manage lesson demand, scheduling, travel constraints, customer messaging, reviews, waitlists, operational reports, and retention-sensitive customer data.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Public booking request form.
- Appointment request admin queue.
- Review request detail with participant responses.
- Waitlist admin queue.

## Technical Shape

- Service provider: `Capell\Bookings\Providers\BookingsServiceProvider`.
- Config: `packages/bookings/config/capell-bookings.php`.
- Routes: public request, calendar feed, opaque portal lessons, opaque consent, tokenized proposals, tokenized review requests, tokenized review participants, and guarded webhook ingestion.
- Admin resources: booking setup, appointment queue, day planner, group sessions, message logs, review requests, travel observations, work zones, owner prompts, change proposals, and waitlist entries.
- Actions: availability, requests, confirmations, reminders, portal links, consent, travel, work zones, payment gates, group sessions, reviews, webhook ingestion, waitlists, skill progress, bundles, cancellation, reporting, import, suppression, risk scoring, owner digests, GDPR export/erasure, and retention pruning.
- Security: customer-facing portal/review/proposal URLs use temporary signatures plus opaque encrypted payloads or hashed tokens; public routes do not expose database IDs.
- Health: `Capell\Bookings\Health\BookingsHealthCheck` verifies tables, morph aliases, and action discoverability.

## Data Model

Required tables include booking setup tables, `appointment_requests`, audit logs, lesson notes, messaging consent/logs, travel observations/adjustments, work zones, change proposals, group sessions, review requests/participants, owner prompts, webhook events, waitlist entries, skill assessments, and lesson bundles.

Token fields are hashed at rest for change proposal parties, review requests, and review participants. Message logs and travel observations are covered by package retention settings.

## Install Impact

- Admin navigation: adds package-owned Filament resources under Bookings.
- Permissions: declared for setup CRUD, appointment updates, planner/message/review/travel visibility, work zones, owner prompts, change proposals, and waitlist CRUD.
- Public routes: signed or tokenized where customer-specific data is visible; booking request submissions and webhooks are throttled.
- Settings: `Capell\Bookings\Settings\BookingsSettings`.
- Queues/schedules: reminder dispatch, workflow expiry, review scheduling, and retention pruning commands are registered.
- Cache tags: `bookings`.

## Common Pitfalls

- Configure `capell-bookings.webhook_tokens` before enabling provider webhook endpoints.
- Use generated Actions for portal, consent, proposal, and review links; do not hand-build URLs.
- Keep public Blade free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Run package commands from the host app; in this repository use `vendor/bin/pest` for package tests.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, Composer metadata, and package install state | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen fails on a missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun focused package tests |
| Portal link returns 403 | Temporary signature expired or URL was hand-built | Check generated Action output and `expires` query value | Regenerate the link through the relevant Action |
| Webhook returns 403 | Provider token is missing or wrong | Check `capell-bookings.webhook_tokens.{provider}` | Configure the token and retry |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary regressed | Check public Blade and public-output tests | Move data loading out of Blade and rerun package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/bookings`.
2. Run the required setup from the host Capell app.
3. Configure booking settings, availability, webhook tokens if used, and staff/calendar surfaces.
4. Open the Bookings admin resources and verify the public `/bookings` route.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- Focused tests: `vendor/bin/pest packages/bookings/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->

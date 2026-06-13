# Bookings

<!-- prettier-ignore-start -->

Bookings is the Capell Operations package for appointment and lesson-based businesses. It starts as a clean public request form and grows into availability, confirmations, reminders, travel-aware planning, group sessions, reviews, waitlists, reporting, and retention workflows without turning the site into a booking-system project.

It ships as `capell-app/bookings`, lives inside the Laravel application like any Capell package, and keeps the public frontend owned by the site team.

## The Short Version

- **For site owners:** customers can request appointments, the team can manage demand, and future scheduling rules are changed in one place.
- **For operators:** the Bookings admin area gives you setup, requests, messages, reviews, waitlists, travel signals, and follow-up surfaces without asking you to edit the website.
- **For developers:** booking logic stays in package Actions, models, migrations, Filament resources, signed/tokenized routes, and documented extension metadata.
- **For agencies:** start with the same reliable workflow on every client, then enable only the advanced pieces a client actually needs.

## Start Simple, Add Depth Later

Bookings is intentionally progressive. A small site can use only the first layer; a busier operation can turn on the rest.

1. **Take requests:** configure services, staff, locations, availability, and the public `/bookings` form.
2. **Run the queue:** review appointment requests, confirm or cancel them, and keep an audit trail.
3. **Keep customers informed:** send reminders, consent-aware messages, calendar feeds, portal lesson links, and change proposals.
4. **Handle real operations:** use travel observations, work zones, provisional holds, payment gates, group sessions, waitlists, and weather/cancellation prompts.
5. **Improve over time:** collect multi-participant reviews, track lesson progress, import clinic attendance, score risk, build fuel/service-area reports, and prune old data.

## What It Adds

| Area | What the package gives you |
| --- | --- |
| Public booking | Branded request form, availability validation, capacity checks, timezone handling, and browser-tested public screenshots. |
| Admin workflow | Setup resources, appointment queue, day planner, group sessions, message logs, reviews, waitlist, prompts, proposals, travel observations, and work zones. |
| Customer links | Temporary signed links for portal lessons, consent, change proposals, review requests, and participant reviews without exposing database IDs. |
| Operations | Travel-aware planning, fuel allowance reporting, service-area heatmaps, clinic attendance import, cancellation fees, and retention pruning. |
| Growth | Optional integration points for payments, notifications, AI advice, events, customer portal, media, and SEO without patching Capell core. |

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Public booking request form.
- Appointment request admin queue.
- Equi Dynamics desktop and mobile booking form.
- Equi Dynamics successful booking submission.

## Technical Shape

- Service provider: `Capell\Bookings\Providers\BookingsServiceProvider`.
- Config: `packages/bookings/config/capell-bookings.php`.
- Routes: public request, staff calendar feed, opaque portal lessons, opaque consent, tokenized proposals, tokenized review requests, tokenized participant reviews, and guarded webhook ingestion.
- Admin resources: booking setup, appointment queue, day planner, group sessions, message logs, review requests, travel observations, work zones, owner prompts, change proposals, and waitlist entries.
- Actions: availability, requests, confirmations, reminders, portal links, consent, travel, work zones, payment gates, group sessions, reviews, webhook ingestion, waitlists, skill progress, bundles, cancellation, reporting, import, suppression, risk scoring, owner digests, GDPR export/erasure, and retention pruning.
- Health: `Capell\Bookings\Health\BookingsHealthCheck` verifies tables, morph aliases, and action discoverability.

## Safety Boundaries

Customer-specific URLs use temporary signatures plus opaque encrypted payloads or hashed tokens. Public routes do not expose database IDs. Webhook tokens are supplied through headers or bearer auth rather than URL paths.

Public Blade views must remain ordinary customer-facing output: no authoring markers, model IDs, field paths, permissions, package internals, signed editor URLs, or database queries.

## Install Impact

- Admin navigation adds Bookings resources under the Bookings group.
- Permissions cover setup CRUD, appointment updates, planner/message/review/travel visibility, work zones, owner prompts, change proposals, and waitlist CRUD.
- Public request submissions and webhooks are throttled.
- Settings live in `Capell\Bookings\Settings\BookingsSettings`.
- Scheduled jobs handle reminder dispatch, workflow expiry, review scheduling, and retention pruning.

## Common Pitfalls

- Do not enable every advanced feature on day one. Start with request, availability, and appointment queue; then add operations features as the client needs them.
- Configure `capell-bookings.webhook_tokens` before exposing provider webhook endpoints.
- Generate portal, consent, proposal, and review URLs through Actions; do not hand-build them.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Quick Start

1. Install the package: `composer require capell-app/bookings`.
2. Run host package setup and migrations.
3. Configure services, staff, locations, availability windows, and booking settings.
4. Open `/bookings` and submit a test request.
5. Confirm the request in the Bookings admin queue.
6. Add reminders, reviews, waitlists, payments, travel, or reporting only when the operation needs them.

## More Detail

- [Adoption guide](docs/adoption-guide.md)
- [Package docs](docs/README.md)
- [Technical overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- Focused tests: `vendor/bin/pest packages/bookings/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->

# Newsletter

Status: **Available, schema-owning** · Kind: **package** · Tier: **premium** · Bundle: **newsletter** · Contexts: **admin, frontend** · Product group: **Capell Marketing**

Newsletter manages audience capture, subscriber records, consent evidence, provider connections, provider sync attempts, segments, imports, campaign sends, preference center updates, UTM attribution, and public subscription lifecycle routes.

## Install

```bash
composer require capell-app/newsletter
```

The package requires `capell-app/admin`, `capell-app/core`, `capell-app/form-builder`, `capell-app/frontend`, and `capell-app/tags`.

## Admin Surfaces

- `SubscriberResource`
- `ProviderConnectionResource`
- `ProviderAudienceResource`
- `ProviderInterestMappingResource`
- `FormMappingResource`
- `NewsletterTagResource`
- `SegmentResource`
- `ImportBatchResource`
- `SyncAttemptResource`
- `NewsletterSendResource`
- Newsletter overview stats for subscribed, pending, and failed/retry-scheduled sync attempts.
- Newsletter settings schema.

## Frontend Surfaces

- `GET /newsletter/confirm/{token}` for subscription confirmation.
- `GET /newsletter/unsubscribe/{token}` for unsubscribe requests.
- `GET /newsletter/preferences/{token}` for public preference center display.
- `POST /newsletter/preferences/{token}` for public preference center updates.
- `POST /newsletter/providers/{providerConnection}/webhook` for provider webhooks.

Public routes should expose only confirmation/unsubscribe/webhook outcomes and must not leak admin labels, provider secrets, subscriber internals, or form-builder mapping details.

## Segments And Preferences

- `EvaluateNewsletterSegmentAction` resolves static and dynamic subscriber segments for targeted sends and automation.
- `CreatePreferenceCenterTokenAction`, `ResolvePreferenceCenterAction`, and `UpdatePreferenceCenterAction` own public preference center state.
- `CreateUnsubscribeTokenAction` and `UnsubscribeSubscriberAction` own unsubscribe flows without requiring Campaign Studio or Form Builder to understand newsletter internals.
- `ResolveUtmAttributionAction` normalizes campaign attribution for newsletter sends and conversion reporting.

## Campaign Sends

- `ScheduleNewsletterSendAction` creates scheduled campaign send records with segment/provider audience and UTM metadata.
- `BuildDueNewsletterSendsAction` returns due scheduled sends in deterministic schedule order for workers or Automation Studio.
- `UpdateNewsletterSendStatusAction` records lifecycle transitions for sending, sent, failed, and cancelled states with timestamps and delivery metadata.
- Scheduled sends contribute to Publishing Studio's editorial calendar as `newsletter.send` events when Publishing Studio is installed.

## Screenshot Plan

- Subscribers admin index.
- Create/edit subscriber form.
- Provider connections admin index and form.
- Provider audiences admin index and form.
- Provider interest mappings admin index and form.
- Form mappings admin index and form.
- Newsletter tags admin index and form.
- Segments admin index and form.
- Import batches admin index.
- Sync attempts admin index.
- Newsletter overview stats on the admin dashboard.
- Confirmation route response.
- Unsubscribe route response.

## Verification

- Package tests: `vendor/bin/pest packages/newsletter/tests --configuration=phpunit.xml`.
- Harness install: `composer require capell-app/newsletter:4.x-dev -W`, then `php artisan package:discover --ansi` and `php artisan migrate --graceful --ansi`.

## Known Risks

- Screenshot capture needs seeded subscribers, provider connections, form mappings, segments, and sync attempts to avoid empty tables.
- Public confirmation/unsubscribe screenshots need disposable public tokens.
- Webhook coverage should use a signed or provider-authenticated request scenario rather than a browser-only screenshot.

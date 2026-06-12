# Capell Payments

Payments gives Capell a provider-neutral payment record layer with native Stripe Checkout, Stripe webhooks, paid downloads, subscription entitlement checks, and portal billing links.

## At A Glance

| Field            | Value                                                                                                 |
| ---------------- | ----------------------------------------------------------------------------------------------------- |
| Composer package | `capell-app/payments`                                                                                 |
| Namespace        | `Capell\Payments`                                                                                     |
| Product group    | Capell Commerce, premium commerce bundle                                                              |
| Surfaces         | Admin, frontend, console                                                                              |
| Providers        | `Capell\Payments\Providers\PaymentsServiceProvider`, `Capell\Payments\Providers\AdminServiceProvider` |
| Requires         | `capell-app/admin`, `capell-app/core`                                                                 |
| Supports         | Access Gate, Customer Portal, Form Builder                                                            |
| Settings         | `Capell\Payments\Settings\PaymentsSettings`                                                           |
| Webhook queue    | `CAPELL_PAYMENTS_WEBHOOK_QUEUE` or `capell-payments.webhooks.queue`                                   |

## Why It Helps Your Capell Workflow

Owners can take payments, donations, subscriptions, and paid-download purchases without moving the commercial workflow outside Capell. Admin users get read-only audit resources for checkout sessions, payment intents, subscriptions, refunds, disputes, customers, and webhook events.

Developers get a gateway contract, fulfillment handlers, typed Actions, and provider-neutral models. Integrating packages can request checkout URLs or resolve subscription entitlements without importing Stripe-specific code.

## What It Adds

- Stripe Checkout session creation for one-off payments, donations, subscriptions, paid downloads, paid gated access, and Form Builder payment fields.
- Stripe webhook intake and queued processing through `HandleStripeWebhookAction`, `VerifyStripeWebhookSignatureAction`, and `ProcessStripeWebhookEventAction`.
- Paid download entitlement grants and signed download routes.
- Customer Portal billing, payment history, subscription, and paid download self-service providers.
- Access Gate fulfillment support through completed checkout sessions.
- Payment settings schema and health diagnostics for gateway configuration.

## Boundaries

Payments owns payment records, Stripe gateway calls, webhook processing, and fulfillment dispatch. Owning packages contribute fulfillment handlers for package-specific outcomes.

Public routes are signed, customer-authenticated, or webhook-only. They must not expose Filament, package internals, authoring metadata, provider secrets, or raw provider payloads. Form Builder checkout return URLs are host allow-listed through `capell-payments.form_builder.allowed_return_hosts`.

## Runtime Surface

- Providers: `src/Providers/`
- Gateway and fulfillment contracts: `src/Contracts/`
- Stripe gateway: `src/Support/Gateways/StripePaymentGateway.php`
- Controllers: `src/Http/Controllers/`
- Actions: `src/Actions/`
- Settings: `src/Settings/PaymentsSettings.php`
- Admin resources: `src/Filament/Resources/`
- Models: `src/Models/`
- Tests: `packages/payments/tests`

## Docs

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Improvement plan](docs/improvement-plan.md)
- [Screenshots contract](docs/screenshots.json)

## Testing

```bash
vendor/bin/pest packages/payments/tests --configuration=phpunit.xml
```

## Troubleshooting

| Symptom                               | Likely cause                                                              | Check                                                                                          | Fix                                                                                    |
| ------------------------------------- | ------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------- |
| Stripe Checkout cannot be created     | Missing Stripe key, price, mode, or allowed return host                   | Check `PaymentsSettings` and the `capell-payments` config in the host app                      | Configure Stripe settings and rerun the focused payment Action test                    |
| Webhooks persist but do not reconcile | Queue worker is not processing the configured payments queue              | Inspect `payment_webhook_events.status` and the queue named by `CAPELL_PAYMENTS_WEBHOOK_QUEUE` | Start the worker for the payments queue or reprocess stored events                     |
| Stripe rejects a webhook              | Signature secret mismatch or raw request body changed before verification | Check `StripeWebhookController` logs and the configured webhook secret                         | Update the webhook secret and replay the event from Stripe                             |
| Paid download URL fails               | Entitlement is missing, expired, or the signed URL was modified           | Check `payment_download_entitlements` for the email/session                                    | Regenerate access through `CreatePaidDownloadUrlAction` or the owning fulfillment flow |

# Customer Portal

Customer Portal turns a Capell site into an authenticated self-service hub for profile data, contributed dashboard items, preferences, support requests, and recent support history.

## At A Glance

| Field             | Value                                                                                                                   |
| ----------------- | ----------------------------------------------------------------------------------------------------------------------- |
| Composer package  | `capell-app/customer-portal`                                                                                            |
| Namespace         | `Capell\CustomerPortal`                                                                                                 |
| Product group     | Capell Customer                                                                                                         |
| Surfaces          | Authenticated frontend, admin                                                                                           |
| Providers         | `Capell\CustomerPortal\Providers\CustomerPortalServiceProvider`, `Capell\CustomerPortal\Providers\AdminServiceProvider` |
| Public route area | `/portal` behind configured `web` and `auth` middleware                                                                 |
| Admin resource    | Portal support requests                                                                                                 |
| Extension points  | Profile, preferences, dashboard item, and self-service item registries                                                  |

## Why It Helps Your Capell Workflow

Owners get a customer-facing account surface without each package building its own dashboard. Customers can see profile data, preferences, support threads, and self-service links in one authenticated place.

Developers get registries for package-contributed cards and self-service items, while each owning package keeps its billing, documents, events, gated access, or newsletter operations behind its own Actions.

## What It Adds

- Site-scoped `PortalAccount` records with encrypted profile/preference storage.
- Authenticated frontend portal routes and controllers.
- Registries for profile providers, preference providers, dashboard items, and self-service items.
- Support request submission, threaded replies, status updates, events, and notifications.
- Filament support-request triage resource.
- DTOs for portal profiles, dashboard items, preferences, self-service items, and support request data.

## Boundaries

Customer Portal is a BYO-auth package. It does not install login, registration, password reset, magic-link, or SSO screens. Pair it with the host app auth stack, Fortify, Socialite, Access Gate, or another identity package.

The portal aggregates customer-facing links and cards; owning packages keep their domain operations. Provider output must not contain internal IDs, tokens, admin URLs, selectors, package internals, signed editor URLs, or authoring metadata.

## Runtime Surface

- Providers: `src/Providers/`
- Contracts: `src/Contracts/`
- Registries: `src/Support/`
- Controllers: `src/Http/Controllers/`
- Actions: `src/Actions/`
- Data objects: `src/Data/`
- Events and notifications: `src/Events/`, `src/Notifications/`
- Admin resource: `src/Filament/Resources/`
- Tests: `packages/customer-portal/tests`

## Docs

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Foundation](docs/foundation.md)
- [Improvement plan](docs/improvement-plan.md)
- [Screenshots contract](docs/screenshots.json)

## Testing

```bash
vendor/bin/pest packages/customer-portal/tests --configuration=phpunit.xml
```

## Troubleshooting

| Symptom                            | Likely cause                                                       | Check                                                        | Fix                                                                           |
| ---------------------------------- | ------------------------------------------------------------------ | ------------------------------------------------------------ | ----------------------------------------------------------------------------- |
| Portal redirects to login          | Host auth middleware cannot resolve an authenticated user          | Check the route middleware stack and current Laravel guard   | Configure the host auth stack before enabling `/portal`                       |
| Dashboard cards are missing        | Provider was not registered or exceeded per-provider/global limits | Check `PortalDashboardItemRegistry` and portal config limits | Register the provider and adjust limits if the output is intentionally larger |
| Support notifications are not sent | No requester email or queue/mail transport is unavailable          | Check support request email fields and queued notifications  | Configure mail/queue in the host app and retry through the support workflow   |

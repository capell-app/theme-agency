# Customer Portal Overview

Status: **Available, schema-owning** · Kind: **package** · Tier: **premium** · Bundle: **content-product** · Contexts: **admin, frontend** · Product group: **Capell Content**

Customer Portal turns a Capell site into an authenticated customer self-service hub. Customers get a private dashboard for profile details, preferences, support requests, and links contributed by other packages.

## Non-Technical Overview

Use Customer Portal when a site needs a logged-in area without every package inventing its own customer dashboard. The package owns the account boundary, dashboard shell, preference form, support request workflow, and staff triage screen.

Other packages can add customer-facing cards or links:

- Payments can expose invoices, payment methods, subscriptions, or checkout links.
- Document Lifecycle can expose controlled downloads.
- Events can expose registrations.
- Access Gate can expose gated resources.
- Newsletter can expose preference or subscription links.

Customer Portal is intentionally BYO-auth. It relies on the host app's authenticated middleware and does not ship login, registration, password reset, magic-link, or SSO screens.

## What This Package Adds

- Authenticated frontend routes under the configured portal path.
- `PortalAccount`, `PortalSupportRequest`, and `PortalSupportRequestReply` models.
- Provider registries for profile fields, dashboard items, self-service items, and preferences.
- Support request submission, replies, status transitions, and requester notifications.
- A Filament support-request triage resource for staff.
- Public-output safeguards: private portal responses are no-store/noindex and should not expose package names, editor URLs, admin selectors, account IDs, or unsafe profile fields.

## Developer Deep Dive

Use these extension points instead of importing another package's internals into Customer Portal:

| Need                      | Entry point                                                          |
| ------------------------- | -------------------------------------------------------------------- |
| Customer profile fields   | `PortalProfileProviderRegistry` with `PortalProfileProvider`         |
| Dashboard cards           | `PortalDashboardItemRegistry` with `PortalDashboardItemProvider`     |
| Self-service feed entries | `PortalSelfServiceItemRegistry` with `PortalSelfServiceItemProvider` |
| Preference options        | `PortalPreferencesProviderRegistry` with `PortalPreferencesProvider` |
| Resolve portal account    | `ResolveAuthenticatedPortalAccountAction`                            |
| Submit support request    | `SubmitSupportRequestAction`                                         |
| Update preferences        | `UpdatePortalPreferencesAction`                                      |

Provider output must be customer-facing. Do not include internal model IDs, signed admin URLs, tokens, selectors, package internals, or authoring metadata.

## Example Dashboard Provider

```php
use Capell\CustomerPortal\Contracts\PortalDashboardItemProvider;
use Capell\CustomerPortal\Data\PortalDashboardItemData;
use Capell\CustomerPortal\Enums\PortalDashboardItemPriority;
use Capell\CustomerPortal\Models\PortalAccount;

final class BillingDashboardItemProvider implements PortalDashboardItemProvider
{
    public function dashboardItemsFor(PortalAccount $portalAccount): iterable
    {
        yield new PortalDashboardItemData(
            key: 'billing.open-invoices',
            label: __('capell-payments::portal.open_invoices'),
            description: __('capell-payments::portal.open_invoices_description'),
            url: route('billing.invoices.index'),
            priority: PortalDashboardItemPriority::High,
        );
    }
}
```

## Boundaries

- Customer Portal owns account/profile/preference/dashboard aggregation and support request workflows.
- Billing, documents, gated resources, events, and newsletter operations stay with the owning package.
- Public Blade receives hydrated arrays and data objects. Do not query models directly from portal views.
- The package does not own customer authentication screens.

## Screenshot Plan

`docs/screenshots.json` covers the authenticated dashboard, preferences/support workflow, support triage list, and support triage detail screen.

## Verification

```bash
vendor/bin/pest packages/customer-portal/tests --configuration=phpunit.xml
vendor/bin/phpstan analyse packages/customer-portal/src packages/customer-portal/tests --configuration=phpstan.neon --memory-limit=1G
vendor/bin/pint --test packages/customer-portal/src packages/customer-portal/tests
```

# Package extension platform

Capell packages contribute behaviour through three canonical registrars. Reach
for these instead of facades, raw container tags, or `afterResolving` hooks.
The manifest (`capell.json`) remains the declarative source of truth; the
registrars are how the declared surfaces are wired at runtime.

## The three registrars

| Surface                                                                                        | Registrar                 | How to reach it                                               |
| ---------------------------------------------------------------------------------------------- | ------------------------- | ------------------------------------------------------------- |
| Core (page types, components, models, interceptors, subscribers, settings)                     | `PackageSurfaceRegistrar` | `$this->surface()` inside an `AbstractPackageServiceProvider` |
| Admin (pages, resources, widgets, user form/table extenders, settings groups, extension pages) | `AdminBridgeRegistrar`    | the `$registrar` argument of an `AdminBridge::register()`     |
| Frontend render hooks                                                                          | `FrontendHookRegistrar`   | resolve `FrontendHookRegistrar` and call `contribute(...)`    |

### Core surfaces

```php
$this->surface()
    ->pageType(new PageTypeData(name: 'event', model: Event::class))
    ->models([Event::class, Venue::class])
    ->settingsClass('events', EventsSettings::class);
```

### Admin surfaces

Admin bridges receive the registrar; never call the `CapellAdmin` facade or
`app()->tag(...)` directly. Every escape hatch the bridges previously used now
has a method:

```php
public function register(AdminBridgeRegistrar $registrar, AdminBridgeContextData $context): void
{
    $registrar->page(SettingsPage::class);
    $registrar->userFormExtender(MyUserFormExtender::class);
    $registrar->userTableExtender(MyUserTableExtender::class);
    $registrar->dashboardSettingsContributor(MyContributor::class);
    $registrar->extensionPage($context->packageName, MyExtensionPage::class);
    $registrar->extensionManagementSurface(ExtensionManagementSurfaceData::settings(/* ... */));
}
```

### Render hooks

`FrontendHookRegistrar::contribute()` takes an `owner` and a stable `key`. The
key gives the platform dedupe (repeated boots cannot double-render) and
diagnostics for free, so you no longer need hand-rolled `WeakMap` guards.

```php
resolve(FrontendHookRegistrar::class)->contribute(
    location: RenderHookLocation::Footer,
    extension: FooterBannerHook::class,
    owner: 'capell-app/campaign-studio',
    key: 'footer-banner',
    cacheSafe: false,
);
```

## Raw extension tags are banned

`tests/Packages/Arch/RawStringExtensionTagBanTest.php` fails the build if any
package source passes a raw dotted tag string to a container `->tag()` call.
Always reference the owning contract constant (e.g. `UserFormExtender::TAG`,
`PaymentFulfillmentHandler::TAG`) or, better, the registrar method above.

# Creating A Capell Theme

For tiering, database ownership, Layout Builder asset boundaries, and cross-theme change rules, start with the [Capell Theme Scale](theme-scale.md). This page is the implementation companion for creating one theme package.

Capell themes are ordinary Composer packages. They register a frontend renderer,
declare package metadata in `capell.json`, and optionally extend another theme.
There is no separate Theme Studio metapackage to install, even though the runtime
classes currently live under the `Capell\Core\ThemeStudio` namespace.

Most new themes should extend `capell-app/theme-foundation`. Foundation Theme
owns the shared Blade, Tailwind, media, settings, and runtime pieces. A child
theme should mostly provide a theme definition, presets, a page wrapper, and the
section views it intentionally customises.

The runtime layers theme data in this order:

1. Parent package preset defaults.
2. Child package preset defaults.
3. Database edits from the Theme admin page.

Database edits always win. Section renderers resolve through the parent theme
chain, so a child theme can override a few sections and inherit the rest from
Foundation. Register full section sets only when the theme deliberately owns all
of those views.

## Before You Start: Check the Catalogue

Every new theme must begin with [docs/themes.json](themes.json), the canonical
theme catalogue. It records each theme's family, lane, tier, sections, and
customisation surfaces, and it is the machine-readable record of what the theme
programme already covers. Do not open an editor for Blade or a service provider
until the catalogue questions below have answers.

New theme checklist:

1. **Pick a family.** Choose an existing `family` from the catalogue, or write
   down why a new family is justified before creating one.
2. **Check the lane.** Confirm the proposed `lane` does not duplicate an
   existing premium theme's lane, and review the `overlapRisk` of neighbouring
   entries in the same family. A high-overlap lane needs a merge or a sharper
   positioning, not another near-identical theme.
3. **Declare positioning.** Decide free versus premium (`tier`) before
   implementation, not after the views exist.
4. **List customisation surfaces.** Fill in the `customisationSurfaces` fields
   (header, footer, Theme Studio tokens, Layout Builder areas, section
   variants, page widget assets, optional integrations) before writing any
   Blade. See [Customisation Contract](#customisation-contract).
5. **Split inherited from custom.** Identify which sections inherit Foundation
   chrome and which are custom to the theme, and record them in
   `standardSections` and `customSections`.

The catalogue entry is a required first artifact:
`packages/theme-foundation/tests/Unit/ThemeCatalogueTest.php` enforces that no
theme package can exist without a matching `docs/themes.json` entry, so a theme
package without one fails the suite. For tiering rules and cross-theme change
rules, see the [Capell Theme Scale](theme-scale.md).

## Existing Packages

Use these packages as the working examples:

| Package                         | Theme key      | Role                                                                                                                               |
| ------------------------------- | -------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| `capell-app/theme-foundation`   | `default`      | Free shared runtime, default Blade components, Tailwind asset generation, settings, media URL handling, and generic beacon client. |
| `capell-app/theme-liquid-glass` | `liquid-glass` | Free modern glass renderer for launch, service, and design-led sites using the standard section set.                               |

Theme packages are intentionally thin. They have no migrations, routes, models,
admin navigation, or settings of their own. They register renderer contracts,
consume the Foundation Theme runtime, and expose demo commands through the same
manifest path used by the Extensions installer demo option and the full Capell
demo install.

## Project-Local Client Themes

Use a project-local theme when the theme belongs to one Laravel app or one
client build, not the reusable Capell marketplace. This is the shape used for
fresh installs that need a bespoke brand while still testing normal Capell
packages.

Recommended host-app layout:

```text
packages/client-theme/
    capell.json
    composer.json
    src/ClientThemeServiceProvider.php
    resources/views/page.blade.php
    resources/views/sections/*.blade.php
    resources/css/client-theme.css
```

Add it as a path repository in the host `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/*",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "vendor/client-theme": "4.x-dev"
    }
}
```

For first-party or marketplace-ready packages, keep the runtime provider behind
`CapellCore::isPackageInstalled()`. For app-local themes, register views and the
Theme Registry definition unconditionally from the provider. The site still uses
the theme only when the database `themes` record and selected layout point at
that theme key, but unconditional registration avoids a common fresh-install
test problem: the provider boots before the seed action marks the local package
installed.

Still include `capell.json`. The installer, doctor command, theme validator,
and package catalogue use the manifest as the source of truth for package name,
theme key, dependencies, and marketplace metadata.

## 1. Choose The Theme Shape

Use Foundation Theme directly when a site only needs the default look:

```json
{
    "name": "capell-app/theme-foundation",
    "kind": "theme",
    "themeKey": "default"
}
```

Create a child theme when you want Foundation's rendering surface with a new
visual treatment:

```json
{
    "name": "vendor/theme-client",
    "kind": "theme",
    "themeKey": "client",
    "extends": "capell-app/theme-foundation",
    "dependencies": {
        "requires": ["capell-app/core", "capell-app/theme-foundation"],
        "supports": [],
        "conflicts": []
    }
}
```

Create a fully separate theme only when Foundation's contract is the wrong base.
It should still declare `kind: "theme"` and a stable `themeKey`.

## 2. Add Package Metadata

Add `capell.json` beside the package `composer.json`. Capell currently uses the
manifest v3 shape shown below.

```json
{
    "manifest-version": 3,
    "name": "vendor/theme-client",
    "slug": "theme-client",
    "displayName": "Theme Client",
    "kind": "theme",
    "capellApiVersion": "^4.0",
    "version": "4.x-dev",
    "description": "Client Theme registers the client theme key and renderer views.",
    "product": {
        "group": "Capell Themes",
        "tier": "premium",
        "bundle": "themes"
    },
    "namespace": "Vendor\\ClientTheme",
    "themeKey": "client",
    "extends": "capell-app/theme-foundation",
    "surfaces": ["frontend"],
    "dependencies": {
        "requires": ["capell-app/core", "capell-app/theme-foundation"],
        "supports": [],
        "conflicts": []
    },
    "providers": {
        "metadata": [],
        "install": [],
        "runtime": ["Vendor\\ClientTheme\\ClientThemeServiceProvider"],
        "admin": [],
        "frontend": []
    },
    "database": {
        "migrations": false,
        "settings": false,
        "requiredTables": []
    },
    "commands": {
        "install": null,
        "setup": null,
        "demo": null,
        "demoParams": [],
        "doctor": null
    },
    "settings": [],
    "permissions": [],
    "capabilities": [],
    "performance": {
        "frontendRenderBudgetMs": 20,
        "adminQueryBudget": 0,
        "cacheTags": ["theme-client"],
        "cacheSafety": {
            "cacheable": false,
            "variesBy": ["site", "locale"],
            "sensitiveOutput": false,
            "invalidationSources": [],
            "queueInvalidation": false
        }
    },
    "healthChecks": [],
    "commercial": {
        "proposedLicense": "paid",
        "requestedCertification": "first-party",
        "supportPolicy": "priority",
        "privateDocsRequested": true
    },
    "marketplace": {
        "summary": "Client Theme gives a Capell site a client-specific renderer.",
        "description": "Client Theme extends Foundation Theme with client-specific public sections while keeping public output cache-safe and editor-free.",
        "screenshots": [],
        "categories": ["frontend", "themes"]
    }
}
```

`themeKey` is the key stored on the `themes` table and selected during install.
Keep it explicit. Renaming a theme key is a content migration, not a cosmetic
package rename.

## 3. Register The Package

In the service provider, keep registration split by responsibility:

- `register()` tells Capell this Composer package exists and is a theme.
- `boot()` checks the package is installed, loads package views, and registers
  the runtime definition and renderers.

That installed-package gate is correct for reusable packages. For project-local
client themes, omit the early return in `boot()` so tests and seed actions can
render the theme during the same process that installs the package record.

```php
<?php

declare(strict_types=1);

namespace Vendor\ClientTheme;

use Capell\Core\Enums\PackageTypeEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Illuminate\Support\ServiceProvider;

final class ClientThemeServiceProvider extends ServiceProvider
{
    public const THEME_KEY = 'client';

    public static string $packageName = 'vendor/theme-client';

    public function register(): void
    {
        CapellCore::registerPackage(
            name: self::$packageName,
            type: PackageTypeEnum::Theme,
            path: realpath(__DIR__ . '/..'),
            version: CapellCore::getInstalledPrettyVersion(self::$packageName),
        );
    }

    public function boot(ThemeRegistry $registry): void
    {
        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'vendor-theme-client');

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'vendor-theme-client::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Client',
            description: 'Client-specific renderer for the Foundation Theme runtime.',
            package: self::$packageName,
            previewImage: '/vendor/client-theme/preview.jpg',
            tags: ['Client', 'Foundation'],
            bestFit: ['Client sites'],
            includedSections: ['navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'launch',
                    name: 'Launch',
                    description: 'Balanced starter preset for launch pages.',
                    previewImage: '/vendor/client-theme/preview.jpg',
                    values: [
                        'primaryColor' => '#2563eb',
                        'accentColor' => '#14b8a6',
                        'headingFont' => 'inter',
                        'cardStyle' => 'bordered',
                        'layoutPresentation' => 'structured',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/client.css'],
        );
    }

    /**
     * @return array<string, ViewSectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'vendor-theme-client::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'vendor-theme-client::sections.hero', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'vendor-theme-client::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'vendor-theme-client::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'vendor-theme-client::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'vendor-theme-client::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'vendor-theme-client::sections.footer', failLoudly: true),
        ];
    }
}
```

Use the existing Agency, Corporate, and SaaS providers as the closest examples.

## 4. Define Presets

Presets are package defaults. They should describe the starting point, not every
possible admin edit.

Preset values are merged into `BrandProfileData`. Current supported values are:

- `primaryColor`
- `accentColor`
- `neutralColor`
- `headingFont`
- `bodyFont`
- `spacing`
- `alignment`
- `cardStyle`
- `navigationStyle`
- `layoutPresentation`
- `motionIntensity`
- `mediaTreatment`

When a child theme extends Foundation, parent preset defaults are applied first,
then the child preset fills or replaces values. The Theme admin page stores final
edits in the database and those values override both package layers.

## 5. Register Renderers And Views

Use shared section keys where possible:

- `navigation`
- `hero`
- `features`
- `proof`
- `content-listing`
- `cta`
- `footer`

`BladeThemeRenderer` receives a page layout view and an array of
`ViewSectionRenderer` instances keyed by section key. It renders every section in
`ThemePageData::allSections()`, then passes the combined HTML into the page
layout as `$content`.

`ViewSectionRenderer` calls `$section->toViewData()` and renders the configured
Blade view. Mark first-party package views with `failLoudly: true`; a missing
view in a shipped theme should fail in tests instead of silently returning an
empty fallback section.

Each page wrapper should render the theme key and brand tokens:

```blade
<div
    data-capell-theme="{{ $themeKey }}"
    style="{{ collect($brand->tokens())->map(fn ($value, $token) => $token . ':' . $value)->implode(';') }}"
>
    {!! $content !!}
</div>
```

This makes preset and admin edits available as CSS custom properties such as
`--theme-primary`, `--theme-accent`, and `--theme-heading-font`.

Use `@frontendAsset()` for theme-local CSS, images, fonts, and other files under
the host app's `public/` directory:

```blade
<link
    rel="stylesheet"
    href="@frontendAsset('css/client-theme.css')"
/>

<img
    src="@frontendAsset('images/client-theme/logo.png')"
    alt=""
    width="96"
    height="96"
/>
```

Do not use root-relative asset paths such as `/images/client/logo.png` in public
theme Blade unless you have tested the configured site domain and the local dev
server use the same origin. Capell's frontend head includes a `<base>` tag for
the resolved site domain. That is correct for canonical URLs, but it means
relative and root-relative theme assets can resolve to the configured site
domain instead of the current dev server port. `@frontendAsset()` resolves
against the current request origin, so `php artisan serve --port=8000` and
production domains both load the same public asset path correctly.

If the theme overrides `resources/views/livewire/page/page.blade.php`, keep it
thin. The current premium themes simply call `RenderCurrentThemePageAction::run()`
and leave page adaptation to the frontend package's `ThemePageAdapter` binding.

## 6. Work With Foundation Theme

Foundation Theme provides:

- `capell` Blade namespace and anonymous `capell::...` components.
- Core layout builder rendering views and widget components from the admin/frontend packages.
- Layout Builder area rendering through `capell::layout.area`.
- `capell:frontend-tailwind-assets`.
- Tailwind imports and sources from installed vendor assets.
- `FoundationThemeSettings` for lazy loading and asset minification defaults.
- `CapellUrlGenerator` for media URLs.
- Generic post-load beacon client support.

Child themes should stay on Foundation's shared runtime unless they need their
own section markup. Put branded presentation in the child theme package, not in
Foundation Theme.

### Own Only What You Customise

Make inheritance the default and ownership the exception. A child theme should
register a section renderer only for markup it genuinely customises; everything
else must resolve through the parent chain to Foundation. This keeps chrome
fixes, accessibility work, and public-safety hardening in one place instead of
copied across dozens of themes.

`capell-app/theme-editorial-serif` is the reference example: it owns ten
sections that carry its editorial look, but points `navigation` and `footer`
at Foundation's chrome views
(`capell-theme-foundation::theme.chrome.navigation` and
`capell-theme-foundation::theme.chrome.footer`) instead of duplicating them.
If a theme's header or footer is not a deliberate visual differentiator,
inherit the Foundation chrome and record that choice in the catalogue's
`customisationSurfaces.header` and `customisationSurfaces.footer` fields.

## Customisation Contract

Every theme offers editors a standard set of customisation surfaces. These are
grounded in what Foundation actually provides, and they map one-to-one onto the
`customisationSurfaces` object in [docs/themes.json](themes.json) — the
catalogue is the machine-readable form of this contract, so what you document
there must match what the theme registers.

**Header** (`customisationSurfaces.header`) — pick one:

- Inherit Foundation navigation chrome by pointing the `navigation` renderer at
  `capell-theme-foundation::theme.chrome.navigation`.
- Ship a theme-specific header view when the header is a genuine visual
  differentiator.
- Use the editable Layout Builder `header` area that Foundation registers
  through `LayoutAreaRegistry`, rendered with
  `<x-capell::layout.area area="header" />`, so editors manage header content
  with normal Layout Builder elements.

**Footer** (`customisationSurfaces.footer`) — the same three options: inherit
Foundation footer chrome (`capell-theme-foundation::theme.chrome.footer`), ship
a theme-specific footer view, or render an editable footer/content area where
the theme supports one.

**Visual tokens** (`customisationSurfaces.themeStudioTokens`) — the Theme
Studio customisation surface. Foundation's preset token keys, grouped:

- Colour and surface: `primaryColor`, `accentColor`, `neutralColor`,
  `surfaceColor`, `foregroundColor`.
- Typography: `headingFont`, `bodyFont`, `headingScale`.
- Radius and spacing: `radius`, `spacing`.
- Cards and density: `cardStyle`, `cardDensity`.
- Media treatment: `mediaTreatment`.
- Layout and motion: `layoutPresentation`, `motionIntensity`.

Child themes that also expose `alignment` and `navigationStyle` list those in
their catalogue entry. Do not invent new token keys per theme; extend the
shared vocabulary deliberately or use container theme settings instead.

**Page composition** — the remaining catalogue fields:

- Section order and section variants (`sectionVariants`): which sections the
  theme renders, how many are custom, and any per-section variants.
- Widgets and page assets (`pageWidgetAssets`): CSS and other assets the theme
  ships for pages and widgets.
- CTA placement: where the theme's `cta` section sits in the standard flow.
- Optional integrations (`optionalIntegrations`): companion packages the theme
  renders richer states for when installed.

When any of these surfaces changes in code, update the theme's
`customisationSurfaces` entry in the same change.

## 7. Register Layout Areas

Layout areas are named places where a theme can render normal Layout Builder
containers outside the standard page-body loop. Use them for theme chrome such
as a header, footer, announcement bar, or campaign strip when editors should be
able to manage the content with existing Layout Builder elements.

Foundation Theme registers the built-in `header` area. A child theme can render
that area directly from its header view:

```blade
<x-capell::layout.area area="header" />
```

To add another area, register it from the theme service provider:

```php
<?php

declare(strict_types=1);

namespace Vendor\ClientTheme;

use Capell\LayoutBuilder\Support\LayoutAreas\LayoutAreaRegistry;
use Illuminate\Support\ServiceProvider;

final class ClientThemeServiceProvider extends ServiceProvider
{
    public const THEME_KEY = 'client';

    public function boot(): void
    {
        $this->app->afterResolving(
            LayoutAreaRegistry::class,
            function (LayoutAreaRegistry $registry): void {
                $registry->register(
                    key: 'announcement',
                    label: __('vendor-theme-client::layout_areas.announcement'),
                    themeKey: self::THEME_KEY,
                );
            },
        );
    }
}
```

Editors choose the area on the container settings form. Containers with no
`meta.area` value still render in `main`, so existing layouts keep working.

Do not create hidden main-flow containers to place header or footer content. The
area key is the placement contract, and the theme view is responsible for
rendering that area. Public area Blade must stay query-free and must not expose
editor markers, model IDs, signed admin URLs, field paths, or package/admin
metadata.

## 8. Generate Frontend CSS

Foundation Theme aggregates Tailwind directives from:

- `capell-theme-foundation.tailwind` config.
- Registered vendor Tailwind imports, plugins, sources, and theme colors.
- Service providers implementing Tailwind asset registration.
- The enabled default `Theme` model's configured colors.

From a Capell host app, generate the active/default frontend CSS directive file:

```bash
php artisan capell:frontend-tailwind-assets
```

Generate a report without writing files:

```bash
php artisan capell:frontend-tailwind-assets --report
```

Regenerate one enabled theme:

```bash
php artisan capell:frontend-tailwind-assets --theme-key=client
```

The default output path is `resources/css/capell/frontend.css`. Per-theme output
falls back to a derived filename such as `frontend-client.css`, unless the Theme
model has a valid `output_css` meta value inside the configured CSS directory.

## 9. Keep Public Output Safe

Themes must never expose admin/editor implementation details to public users.
Public Blade, cached HTML, theme CSS, and theme JavaScript must not contain
authoring controls, editable markers, model IDs, field paths, labels,
permissions, package names, selectors, or signed editor URLs.

In-page editing is owned by `capell-app/frontend-authoring`. The public page
loads as normal theme HTML, then a post-load beacon checks the authenticated
user. Only an authenticated admin beacon response may add edit controls or
signed Filament editor URLs.

Use stable selectors that already exist for presentation. Do not add hidden
authoring-only markers to theme markup.

## 10. Install And Select The Theme

The CLI and web installer both understand theme selection:

```bash
php artisan capell:install --packages=vendor/theme-client --theme=client
```

The installer only asks for a theme when there is a real choice:

- More than one selected or installed theme candidate exists.
- A selected non-default theme package needs an explicit active theme.

Marketplace installs use the same package metadata. When Deployments is
installed, Marketplace publishes the Composer change through the deployment
publisher. Without Deployments, it shows the Composer command so the change can
be applied manually.

## Add Active-Theme Layout Container Settings

Themes can add fields to the Layout Builder container editor without adding core
columns or leaking admin schema details into public HTML. Layout Builder stores
these values on the container itself under
`containers.{containerKey}.meta.theme_settings.{themeKey}` and only shows the
fields when the edited layout resolves to the active theme key.

Register the extender through the admin bridge in your theme provider:

```php
<?php

declare(strict_types=1);

namespace Vendor\ClientTheme\Providers;

use Capell\Admin\Support\Bridges\AdminBridgeRegistrar;
use Capell\Core\Facades\CapellCore;
use Illuminate\Support\ServiceProvider;
use Vendor\ClientTheme\Filament\Extenders\ClientLayoutContainerSchemaExtender;

final class ClientThemeServiceProvider extends ServiceProvider
{
    public function boot(AdminBridgeRegistrar $admin): void
    {
        if (! CapellCore::isPackageInstalled('vendor/theme-client')) {
            return;
        }

        $admin->schemaExtender(
            ClientLayoutContainerSchemaExtender::class,
            ClientLayoutContainerSchemaExtender::TAG,
        );
    }
}
```

The extender returns only the fields. Layout Builder wraps them in a labelled
**Theme settings: Client** section and applies the state path:

```php
<?php

declare(strict_types=1);

namespace Vendor\ClientTheme\Filament\Extenders;

use Capell\LayoutBuilder\Contracts\Extenders\LayoutContainerSchemaExtender;
use Capell\LayoutBuilder\Data\LayoutContainerSchemaContextData;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

final class ClientLayoutContainerSchemaExtender implements LayoutContainerSchemaExtender
{
    public function themeKey(): string
    {
        return 'client';
    }

    public function themeLabel(): string
    {
        return __('client-theme::generic.theme_label');
    }

    public function supports(LayoutContainerSchemaContextData $context): bool
    {
        return $context->themeKey === $this->themeKey();
    }

    public function extendContainerComponents(Schema $schema, LayoutContainerSchemaContextData $context): array
    {
        return [
            Select::make('surface_tone')
                ->label(__('client-theme::form.surface_tone'))
                ->options([
                    'default' => __('client-theme::form.surface_tone_default'),
                    'muted' => __('client-theme::form.surface_tone_muted'),
                    'contrast' => __('client-theme::form.surface_tone_contrast'),
                ])
                ->default('default')
                ->native(false),
        ];
    }
}
```

Read the value from hydrated layout container data in public Blade or a theme
renderer. Do not dump raw meta, schema labels, package names, signed URLs, model
IDs, field paths, or permissions:

```blade
@php
    $clientSettings = data_get($container, 'meta.theme_settings.client', []);
    $surfaceTone = is_array($clientSettings)
        ? ($clientSettings['surface_tone'] ?? 'default')
        : 'default';
@endphp

<section
    @class([
        'layout-band',
        'layout-band-muted' => $surfaceTone === 'muted',
        'layout-band-contrast' => $surfaceTone === 'contrast',
    ])
>
    {{ $slot }}
</section>
```

Use this path for per-container presentation choices such as emphasis, section
tone, editorial rhythm, or theme-specific visual modes. If the theme needs real
records, reporting state, or reusable business data, create a companion package
with migrations and render that data through a public-safe Action or payload
contributor instead.

## 11. Test The Theme

At minimum, add tests for:

- `capell.json` declares `kind: "theme"`, manifest v3 fields, a stable
  `themeKey`, and the correct `extends` package.
- The service provider registers the package as `PackageTypeEnum::Theme`.
- The theme registers with `ThemeRegistry` only when the package is installed.
- The definition includes presets, sections, package name, preview image, tags,
  best-fit labels, and assets.
- Section renderers render the expected package views.
- Registered layout areas appear only for the intended theme.
- Containers assigned to theme areas render from the theme chrome, not from the
  main content loop.
- Child defaults layer over parent defaults, and database edits win.
- The page wrapper renders `data-capell-theme` and brand token CSS variables.
- Anonymous and non-admin frontend output exposes no authoring surface.

Run the package tests directly:

```bash
vendor/bin/pest packages/theme-client/tests --configuration=phpunit.xml
```

For installer or marketplace changes, run the matching host-app tests in
`../capell-4`.

## Common Pitfalls

- Do not add admin settings to child themes unless the theme owns genuinely new
  behaviour. Prefer shared `BrandProfileData` fields.
- Do not duplicate Foundation Theme views just to change spacing or colour. Use
  tokens and page wrapper CSS first.
- Do not use root-relative theme asset URLs in Blade when the frontend renders a
  `<base>` tag. Use `@frontendAsset('path/from/public.css')`.
- Do not make public markup depend on `frontend-authoring`.
- Do not rely on a Studio metapackage. Theme packages install independently.
- Do not rename a `themeKey` after content exists without a migration plan.
- Do not forget Tailwind sources for package views. Missing sources produce
  views that render correctly but have purged CSS.

## Useful Future Improvements

These changes would make theme work faster and safer:

- Add a `make:capell-theme` scaffolder that creates `capell.json`, provider,
  page wrapper, section views, and baseline Pest tests from a theme key.
- Add a manifest validation command that checks package metadata, `extends`,
  dependencies, provider classes, and theme keys before release.
- Add a visual theme contract test that renders all standard sections for each
  registered theme and checks for missing views, empty sections, and leaked
  authoring metadata.
- Add an admin preview matrix for theme and preset combinations, with generated
  screenshots stored beside package docs.
- Move the public namespace from `ThemeStudio` to a neutral `Themes` namespace
  over time, keeping aliases for backwards compatibility.
- Document and enforce the supported `BrandProfileData` token vocabulary so
  custom themes do not invent incompatible preset fields.

## Theme Inheritance Runtime

Set runtime inheritance in the service provider definition:

```php
return new ThemeDefinitionData(
    key: 'client',
    name: 'Client',
    description: 'Client-specific Foundation child theme.',
    package: 'vendor/theme-client',
    previewImage: '/vendor/client/theme.jpg',
    tags: ['Client'],
    bestFit: ['Client sites'],
    includedSections: ['navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer'],
    presets: [$preset],
    extends: 'default',
);
```

The manifest still uses the package-level parent:

```json
{
    "kind": "theme",
    "themeKey": "client",
    "extends": "capell-app/theme-foundation"
}
```

A child theme may omit standard section renderers and inherit them from Foundation. It should register only the views it actually customises. Add tests for inherited sections and for the loud failure path when no child or parent renderer exists.

## Marketplace Metadata Expectations

For first-party and marketplace-ready themes, include enough metadata for product-grade admin cards:

- `tags` for quick visual/content style cues.
- `bestFit` for use cases.
- `includedSections` for section count and capability summary.
- `previewImage` and screenshot metadata.
- Optional integration names in admin or manifest metadata when the package renders richer states for installed companions.
- Demo command/action readiness when demos exist.

Admin UI strings must stay in `capell-admin::*`; package public Blade must remain free of package names, authoring controls, signed URLs, model IDs, field paths, permissions, and database queries.

## Current First-Party Child Themes

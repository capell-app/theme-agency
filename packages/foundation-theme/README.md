# Foundation Theme

Capell default theme - ships the standard Tailwind asset pipeline, Blade directives, URL generator, and SVG media component.

## At A Glance

- Package: `capell-app/foundation-theme`
- Namespace: `Capell\FoundationTheme\`
- Surfaces: admin, frontend
- Service provider: `packages/foundation-theme/src/Providers/FoundationThemeServiceProvider.php`
- Capell dependencies: `capell-app/frontend`, `capell-app/layout-builder`
- Third-party dependencies: `lorisleiva/laravel-actions`, `spatie/laravel-data`, `spatie/laravel-package-tools`

## Why It Helps Your Capell Workflow

- Provides the default frontend theme base, asset pipeline, Blade directives, URL generation, and media rendering conventions for Capell sites.
- Helps owners launch a coherent frontend faster while keeping theme-specific presentation out of database content.
- Gives developers the baseline renderer patterns used by premium theme packages and package frontend components.

## Best Used With

- [Layout Builder](../layout-builder/README.md)
- [Theme Agency](../theme-agency/README.md)
- [Theme Corporate](../theme-corporate/README.md)

## What It Adds

- Capell default theme - ships the standard Tailwind asset pipeline, Blade directives, URL generator, and SVG media component.
- Theme Studio registration, frontend Blade chrome, Layout Builder rendering views, widget components, and runtime asset registration.
- Livewire helpers for page listings and page-asset selection where the frontend runtime needs them.
- Package setup and Tailwind asset generation commands.
- A demo seeding command for route-backed Foundation page captures.
- A `header` Layout Builder area so editors can place normal layout widgets inside the Foundation header chrome.
- Seven `kitchen-sink-*` reference widgets for the Demo Kit kitchen sink page: rich text, structured text, data display, forms, interactions, embeds, and utility states.

The kitchen sink widgets are CMS authoring/reference fixtures for inspecting semantic HTML, page-scoped assets, and accessibility contracts. They should not be used as production landing page templates.

## Why It Matters

**For developers:** Provides the baseline Laravel view and asset pipeline that child themes and frontend packages can target.

**For teams:** Gives each Capell installation a standard frontend foundation before a custom or theme renderer is added.

## Built With

This package makes its Composer dependencies visible because they are part of the value proposition, not just plumbing. When an upstream package has a public repository, its linked preview card points readers back to the maintainers so their work gets proper credit.

**Capell packages used here**

- [Capell Frontend](https://github.com/capell-app/frontend)

**Open-source packages used here**

- [Spatie Laravel Package Tools](https://github.com/spatie/laravel-package-tools) - Laravel package bootstrapping for config, migrations, commands, translations, and service provider setup.

**Linked package previews**

[![Spatie Laravel Package Tools GitHub preview](https://opengraph.githubassets.com/capell-readme/spatie/laravel-package-tools)](https://github.com/spatie/laravel-package-tools)

## Screens And Workflow

Screenshot capture targets are defined in [docs/screenshots.json](docs/screenshots.json) during package deployment.

- 12 deployment capture targets cover the Foundation settings screen, Tailwind asset output review, frontend render checks, the header Layout Builder area, and marketplace layout captures.
- 15 marketplace media entries ship in `capell.json`: one extension card image
  and 14 committed runner PNG captures.
- The generated PNG screenshots are committed under `docs/screenshots` for
  settings, frontend, Tailwind output review, and route-backed layout workflows.

## Technical Shape

- FoundationThemeServiceProvider registers theme services and settings.
- Config file: capell-foundation-theme.php.
- Settings migration creates default theme settings.
- Registers the `capell` Blade namespace and anonymous `capell::...` components.
- Registers core layout builder frontend rendering views and widget components.
- Registers the `header` Layout Builder area and renders it from `capell::header.index`.
- Page-listing widgets can opt into request-aware pagination with widget `pagination` and `limit` metadata; latest pages and selected pages use separate query keys.
- Runtime theme data layers parent defaults, child defaults, and database edits in that order.
- GenerateTailwindAssetsCommand writes one frontend Tailwind directive file; runtime theme colours are emitted as CSS variables by the theme head tokens.
- core layout builder JavaScript is registered as a conditional vendor build asset and only loads when the resolved frontend layout contains widgets.
- BladeDirectives and CapellUrlGenerator support rendering.
- The beacon client is generic. It must not ship authoring controls or authoring metadata in theme HTML; `capell-app/frontend-authoring` owns the admin-only response that decorates the page.

## Code Map

| Area      | Path                                      | Purpose                                                           |
| --------- | ----------------------------------------- | ----------------------------------------------------------------- |
| Actions   | `packages/foundation-theme/src/Actions`   | Domain operations. Test these directly where possible.            |
| Enums     | `packages/foundation-theme/src/Enums`     | Persisted states and option values.                               |
| Filament  | `packages/foundation-theme/src/Filament`  | Settings schema surfaced through the main provider.               |
| Livewire  | `packages/foundation-theme/src/Livewire`  | Page listing and page-asset helper components.                    |
| Providers | `packages/foundation-theme/src/Providers` | Registration, extension hooks, routes, migrations, and resources. |
| Resources | `packages/foundation-theme/resources`     | Views, translations, assets, and package resources.               |
| Config    | `packages/foundation-theme/config`        | Package configuration and publishable config.                     |
| Database  | `packages/foundation-theme/database`      | Migrations, seeders, and settings migrations.                     |
| Tests     | `packages/foundation-theme/tests`         | Package-level Pest coverage.                                      |

## Admin Surface

- Settings schema: `FoundationThemeSettingsSchema`.
- Settings data and migrations: `FoundationThemeSettings`, `FoundationThemeSettingsMigrationProvider`.
- Theme chrome choices: Foundation header and footer registrations.

## Runtime Surface

- Frontend Blade namespaces and components under `capell::...` and `capell-foundation-theme::...`.
- Theme Studio definition and Blade renderer for `themeKey: default`.
- Conditional frontend asset registration for CSS, runtime JavaScript, Tailwind imports, and Tailwind sources.
- Livewire helpers: `AbstractAssets`, `AbstractWidget`, `PageAssets`, `Pages`.

## Child Theme Override Contract

Child themes should extend the Foundation package in `capell.json` and use runtime `extends: 'default'` in `ThemeDefinitionData`. Foundation owns the stable base contract below; child themes may override these presentation surfaces without changing persisted content or leaking authoring metadata.

**Section keys:** `navigation`, `hero`, `features`, `proof`, `content-listing`, `cta`, `footer`.

**Shared component views:** `capell::theme.page`, `capell::theme.sections.*`, `capell::header.index`, `capell::footer`, `capell::layout.index`, `capell::layout.main`, `capell::layout.area`, `capell::content`, `capell::button.index`, `capell::media.background`, `capell::media.svg`.

**Runtime CSS tokens:** `--color-brand`, `--color-link`, `--color-link-active`, `--color-divider`, `--foundation-page-bg`, `--foundation-surface-bg`, `--foundation-muted-bg`, `--foundation-header-bg`, `--foundation-border`, `--foundation-border-strong`, `--foundation-card-bg`, `--foundation-primary-action`, `--foundation-band-bg`, `--foundation-band-alt-bg`, `--foundation-band-accent-bg`, `--foundation-band-border`, `--foundation-image-border`, `--foundation-image-radius`, `--foundation-section-spacing`, `--foundation-widget-gap`, `--foundation-radius`.

**Theme chrome areas:** `header` is the supported Layout Builder area for editable chrome. Future chrome areas should follow the same registry + explicit render pattern.

Do not override Foundation internals to add editor controls, query data in public Blade, or move presentation HTML into database content. Add theme-specific markup in child package section views, and keep shared runtime behavior in Foundation.

## Commands

- `capell:foundation-theme-setup {--force : Rebuild Foundation-managed layout defaults}` (packages/foundation-theme/src/Console/Commands/SetupCommand.php)
- `capell:foundation-theme-demo {--site=* : Site name(s) to seed} {--language=* : Language code(s) to seed} {--base-url= : Base URL used for seeded demo links} {--force : Rebuild existing Foundation demo page layouts}` (packages/foundation-theme/src/Console/Commands/DemoCommand.php)

## Data And Persistence

- This package does not create content tables.
- It owns settings through create_foundation_theme_settings.php.
- Theme output depends on core site, page, layout, and media data.

- Config: `packages/foundation-theme/config/capell-foundation-theme.php`.

## Extension Points

- Listeners: `RunTailwindAssetsOnPackageChange`.
- Register Capell extension points, routes, migrations, settings, render hooks, and resources from service providers.

## Layout Builder Areas

Foundation Theme exposes the first non-main Layout Builder area: `header`. Editors still work with ordinary containers and widgets in the Layout Builder, but containers assigned to the header area render in the site header instead of the main page-content loop.

The service provider registers the area with `Capell\LayoutBuilder\Support\LayoutAreas\LayoutAreaRegistry`:

```php
use Capell\LayoutBuilder\Support\LayoutAreas\LayoutAreaRegistry;

$this->app->afterResolving(
    LayoutAreaRegistry::class,
    function (LayoutAreaRegistry $registry): void {
        $registry->register('header', __('capell-layout-builder::generic.header_area'));
    },
);
```

The header view renders the area explicitly:

```blade
<x-capell::layout.area area="header" />
```

Use this pattern for future theme chrome areas such as `footer`, `announcement`, or client-specific header slots. Do not hide containers in the main loop to fake theme placement; set `meta.area`, register the area, and render it from the theme view that owns the chrome.

## Install Impact

- Adds default theme settings.
- Adds Foundation-owned core layout builder defaults when the package setup command runs.
- Adds the `header` Layout Builder area when Layout Builder is installed.
- Adds Tailwind asset generation command.
- Adds config keys for asset build tool, npm dependencies, Tailwind sources, and media URL behaviour.
- No public routes are registered by this package.
- Does not add in-page authoring markup to public Blade or cached HTML.

## Install And Setup

- Install with `composer require capell-app/foundation-theme` in the host Capell application.
- In this repository, verify package changes with `vendor/bin/pest`; do not use `php artisan`.

## Admin And Access

- None proven in this package directory.

## Common Pitfalls

- Regenerate assets after changing source paths, Tailwind plugins, or package CSS imports. Theme colour edits are runtime CSS variables and do not require a rebuild.
- Match asset_build_tool to the host app.
- Set media URL config before production media rendering.
- Treat Foundation Theme as the shared runtime, not the place for client-specific branding.
- Add branded page wrappers and section views in child theme packages such as `theme-agency`, `theme-corporate`, or `theme-saas`.
- Keep authoring behaviour in `capell-app/frontend-authoring`; themes should expose stable presentation selectors, not hidden editor metadata.
- Keep child themes on shared `capell::...` views unless they need their own section markup.

## Docs

- [docs index](docs/README.md)
- [credits-and-acknowledgements.md](docs/credits-and-acknowledgements.md)
- [overview.md](docs/overview.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/foundation-theme/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Theme output is public output. Keep admin-only metadata and editor hooks out of rendered markup.
- Put behaviour changes in `src/Actions/`; UI classes, commands, and controllers should call actions instead of owning domain logic.
- Use backed enums for persisted values and enum labels for Filament options.

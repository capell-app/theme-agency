# Hero Overview

Hero provides the shared default home-page hero widget used by Capell frontend themes. It registers Blade components and a setup command that can seed Hero-managed home layout defaults.

## What It Adds

- `capell::widget.hero` Blade component.
- Anonymous `capell-hero::...` component namespace for hero partials.
- Tailwind import and view-source registration for hero assets.
- `capell:hero-setup` setup command.
- Filament schema extenders for theme, widget, and widget-asset hero background/media settings.
- No package-owned tables, routes, settings, or Filament resources.

## Install Impact

- Requires `capell-app/admin`, `capell-app/core`, `capell-app/frontend`, and `capell-app/layout-builder`.
- Adds no migrations.
- Adds frontend rendering only when a layout/theme uses the Hero widget component or after the setup command creates default layout content.

## Admin Surfaces

Hero does not add a standalone admin page or resource. It does register Filament schema extenders that add hero background and media controls to theme settings, widget display settings, and widget-asset forms.

## Frontend Surfaces

| Surface                     | Use case                                                                  | Screenshot           |
| --------------------------- | ------------------------------------------------------------------------- | -------------------- |
| Home hero widget            | Show the default hero widget rendered in a public theme/page.             | `hero-home-widget`   |
| Hero slide/related partials | Show multi-item hero content if seeded by the setup flow or demo fixture. | `hero-slide-variant` |

## Demo Setup

Install core baseline packages, hard dependencies, and `capell-app/hero`. Run `capell:hero-setup` in the host app if the demo needs seeded layout defaults. Capture a public page using a theme that renders the hero component.

## Screenshot Coverage

The screenshot contract should prove the public widget output and, when seeded, any slide/related content states. There is no standalone admin screen to capture.

## Known Risks

- A package install alone may not create visible output; the setup command or demo content is required.
- The widget relies on a theme/layout to call the component, so captures should name the theme fixture used.
- Hero asset registration should be checked when frontend Tailwind assets are regenerated.

## Verification

Run package tests from the repository root:

```bash
vendor/bin/pest packages/hero/tests --configuration=phpunit.xml
```

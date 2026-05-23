# Theme Business Solutions

Business-focused theme pack for Capell.

## At A Glance

- Package: `capell-app/theme-business-solutions`
- Namespace: `Capell\ThemeStudio\BusinessSolutions\`
- Capell dependencies: `capell-app/core`, `capell-app/foundation-theme`
- Screenshot demo dependency: `capell-app/demo-kit`

## Why It Helps Your Capell Workflow

- Registers vertical business theme keys and renderer profiles for professional, civic, commerce, and modern service sites.
- Helps owners start from a business-specific presentation system instead of adapting a generic theme for every vertical.
- Gives developers a theme package for vertical renderer profiles while keeping content and layout ownership in Capell packages.

## Best Used With

- [Foundation Theme](../foundation-theme/README.md)
- [Theme Corporate](../theme-corporate/README.md)
- [Theme Agency](../theme-agency/README.md)

## What It Adds

- Twelve vertical business themes for legal, healthcare, finance, real estate, education, hospitality, consultancy, nonprofit, manufacturing, government, recruiting, and commerce sites.
- Public views for navigation, hero, features, proof, content listing, CTA, and footer sections.
- Distinct business layouts rather than color-only theme variants.

## Screens And Workflow

Screenshots are generated from [docs/screenshots.json](docs/screenshots.json) during package deployment.

- Theme admin list showing Business Solutions themes.
- Admin content block editing screen for unique page content.
- One frontend screenshot for every `business-*` theme.
- Theme preview URL output.

## Technical Shape

- `BusinessSolutionsThemeServiceProvider` registers twelve renderer-backed theme definitions.
- `capell.json` declares `themeKey: "business-legal"` and `extends: "capell-app/foundation-theme"`.
- Uses Foundation Theme runtime data and standard section keys, while rendering its own page and section Blade views.
- Ships no migrations, config, routes, models, admin navigation, or package-owned settings.
- Public theme output must stay free of package identifiers, signed admin URLs, Filament/editor markers, and other authoring metadata.

## Demo Content

Install `capell-app/demo-kit` in the disposable screenshot app and generate demo content before capture. Unique business copy should be edited through normal Layout Builder content blocks; this package owns the presentation only.

## Docs

- [docs index](docs/README.md)
- [overview.md](docs/overview.md)
- [screenshots.json](docs/screenshots.json)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/theme-business-solutions/tests --configuration=phpunit.xml
```

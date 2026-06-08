# Theme Knowledge

Theme Knowledge is a Capell theme for knowledge bases, publishers, resource
hubs, and content-led teams that need searchable, editorial frontend pages.

## At A Glance

- Package: `capell-app/theme-knowledge`
- Namespace: `Capell\ThemeStudio\Knowledge\`
- Theme key: `knowledge`
- Surfaces: frontend, console
- Service provider:
  `Capell\ThemeStudio\Knowledge\KnowledgeThemeServiceProvider`
- Demo command:
  `capell:theme-knowledge-demo {--url=} {--languages=} {--sites=} {--force}`
- Extends: `capell-app/foundation-theme`
- Database impact: none

## Why It Helps Your Capell Workflow

- Site owners can launch a resource-library site where visitors can browse
  topics, featured content, authors, and search-led journeys.
- Editors get a theme vocabulary that fits documentation, articles, resource
  hubs, newsletters, and author-led content.
- Developers get a renderer package with optional Blog, Search, and Newsletter
  integration checks instead of hard package coupling.

## Best Used With

- [Foundation Theme](../foundation-theme/README.md)
- [Blog](../blog/README.md) for resources and author content.
- [Search](../search/README.md) for public search listing sections.
- [Newsletter](../newsletter/README.md) for subscription CTAs.
- [SEO Suite](../seo-suite/README.md) for structured discovery.

## What It Adds

- Registers the `knowledge` theme definition and preset.
- Ships a knowledge-focused page wrapper and theme CSS.
- Adds section renderers for topic hubs, featured content, resource library,
  doc articles, search listing, newsletter, authors, proof, and supporting
  content blocks.
- Uses Core `ViewSectionRenderer` extra view data for optional Blog, Search, and Newsletter
  sections.
- Provides standalone Knowledge Base index/article templates that consume
  `capell-app/knowledge-base` public DTO arrays when that package is installed.
- Keeps optional package availability checks in the service provider/renderer
  layer, while author and topic hub cards can come from page render data with
  translated defaults.
- Renders docs/article pages with hydrated breadcrumbs, category sidebar,
  article metadata, readable body copy, and a sticky table of contents.
- Adds a demo install command backed by `InstallKnowledgeThemeDemoAction`.
- Adds `ThemeKnowledgeHealthCheck` and a Theme management page contribution.

## Runtime Surface

| Area                | Path                                               |
| ------------------- | -------------------------------------------------- |
| Provider            | `src/KnowledgeThemeServiceProvider.php`            |
| Demo command        | `src/Console/Commands/DemoCommand.php`             |
| Demo action         | `src/Actions/InstallKnowledgeThemeDemoAction.php`  |
| Renderer            | Core `ViewSectionRenderer` extra view data         |
| Theme management    | `src/Manifest/ThemeManagementPageContribution.php` |
| Health check        | `src/Health/ThemeKnowledgeHealthCheck.php`         |
| Views               | `resources/views/page.blade.php`                   |
| CSS                 | `resources/css/theme-knowledge.css`                |
| Screenshot manifest | `docs/screenshots.json`                            |

## Install Impact

- Adds a frontend theme renderer and console demo command.
- Adds no migrations, settings, models, package-owned routes, or admin
  resources.
- Depends on Foundation Theme and reads normal Capell page/theme runtime data.
- Optional sections stay guarded when Blog, Search, or Newsletter are not
  installed.
- Author and topic hub defaults use package translations and accept hydrated
  section items for real site-specific teams and topics.

## Docs

- [Docs index](docs/README.md)
- [Overview](docs/overview.md)
- [Creating a Capell theme](../../docs/creating-a-theme.md)
- [Package Screenshot Automation](../../docs/package-screenshot-automation.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/theme-knowledge/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Keep public theme output free of admin URLs, signed preview URLs, editor
  selectors, theme internals, model IDs, and permission metadata.
- Keep optional package checks inside the service provider/renderer layer, not
  public Blade.
- Keep the docs aligned with `KnowledgeThemeServiceProvider::definition()` when
  section keys, presets, or optional integrations change.

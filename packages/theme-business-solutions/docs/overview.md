# Theme Business Solutions

Status: **Available, no schema impact** · Kind: **theme** · Tier: **premium** · Bundle: **themes** · Contexts: **frontend** · Product group: **Capell Themes**

Theme Business Solutions adds twelve vertical business themes on top of Foundation Theme. Each theme uses the same editable Capell page and Layout Builder content model, but changes the public renderer, layout rhythm, navigation shape, proof placement, and conversion pattern for a specific business category.

## What This Package Adds

- Twelve theme definitions under the `business-*` theme keys.
- Business-specific Blade renderers for navigation, hero, features, proof, content listings, CTA, and footer sections.
- Distinct layouts for legal, healthcare, finance, real estate, education, hospitality, consultancy, nonprofit, manufacturing, civic, recruiting, and commerce sites.
- Screenshot coverage for every business theme and the admin content block editing workflow.
- A renderer-only package that extends Foundation Theme without adding migrations, routes, settings, or public editor metadata.

## Theme Catalogue

| Theme key                | Name     | Business fit                    | Layout              |
| ------------------------ | -------- | ------------------------------- | ------------------- |
| `business-legal`         | Juris    | Legal & Compliance              | `form-hero`         |
| `business-healthcare`    | Careline | Healthcare Clinic               | `appointment-hero`  |
| `business-financial`     | Ledger   | Financial Advisory              | `ticker-hero`       |
| `business-real-estate`   | Habitat  | Real Estate Brokerage           | `search-hero`       |
| `business-education`     | Pathway  | Education & Training            | `program-finder`    |
| `business-hospitality`   | Reserve  | Events & Hospitality            | `booking-hero`      |
| `business-consultancy`   | Atlas    | B2B Services Consultancy        | `split-modern`      |
| `business-nonprofit`     | Beacon   | Nonprofit Impact                | `story-impact`      |
| `business-manufacturing` | Forge    | Manufacturing Supplier          | `spec-hero`         |
| `business-government`    | Civic    | Local Government/Civic Services | `service-directory` |
| `business-recruiting`    | Talent   | Recruiting/HR Agency            | `job-search`        |
| `business-commerce`      | Merchant | Ecommerce/B2B Catalog           | `catalog-grid`      |

## Demo Content And Editing

Screenshot installs should include `capell-app/demo-kit` alongside the core stack, Layout Builder, Foundation Theme, and this package. Run the full demo generator in the disposable app before capture so every frontend screenshot uses real demo page content rather than empty theme chrome.

The business themes do not own custom page copy. Unique content remains editable in the admin as normal Layout Builder content blocks:

- hero eyebrow, heading, summary, and actions;
- feature/service cards;
- proof metrics and trust statements;
- listing/resource items;
- CTA copy and links;
- footer columns and navigation.

That gives editors custom page-specific content while keeping presentation in package Blade views. Public theme output must stay free of editor controls, signed URLs, package identifiers, Filament markers, model IDs, field paths, and admin-only metadata.

## Screenshot Plan

The screenshot contract is stored in [screenshots.json](screenshots.json). Deployment should install the package with Demo Kit content, resolve each admin surface or frontend URL, and write images to `public/docs/screenshots/packages/theme-business-solutions`.

- Themes admin list showing Business Solutions records.
- Admin content block editor showing unique business page content as editable blocks.
- One frontend page for each of the twelve `business-*` themes.
- Theme preview URL output from `capell.admin.theme-preview`.

## Generated Screenshot Gallery

The deployment screenshot runner populates these files from [screenshots.json](screenshots.json). Do not link them as Markdown images until the files are committed under `public/docs/screenshots/packages/theme-business-solutions`.

| Screenshot                                               | Expected file                                           |
| -------------------------------------------------------- | ------------------------------------------------------- |
| Business Solutions themes in admin                       | `theme-admin-list-showing-business-solutions.png`       |
| Unique business content editable as admin content blocks | `admin-content-blocks-edit-unique-business-content.png` |
| Juris legal theme                                        | `frontend-business-legal-theme.png`                     |
| Careline healthcare theme                                | `frontend-business-healthcare-theme.png`                |
| Ledger financial theme                                   | `frontend-business-financial-theme.png`                 |
| Habitat real estate theme                                | `frontend-business-real-estate-theme.png`               |
| Pathway education theme                                  | `frontend-business-education-theme.png`                 |
| Reserve hospitality theme                                | `frontend-business-hospitality-theme.png`               |
| Atlas consultancy theme                                  | `frontend-business-consultancy-theme.png`               |
| Beacon nonprofit theme                                   | `frontend-business-nonprofit-theme.png`                 |
| Forge manufacturing theme                                | `frontend-business-manufacturing-theme.png`             |
| Civic government theme                                   | `frontend-business-government-theme.png`                |
| Talent recruiting theme                                  | `frontend-business-recruiting-theme.png`                |
| Merchant commerce theme                                  | `frontend-business-commerce-theme.png`                  |

## Screenshot Routes

The isolated screenshot harness should expose one disposable route per theme:

| Theme key                | Screenshot target                                       |
| ------------------------ | ------------------------------------------------------- |
| `business-legal`         | `/theme-business-solutions-demo/business-legal`         |
| `business-healthcare`    | `/theme-business-solutions-demo/business-healthcare`    |
| `business-financial`     | `/theme-business-solutions-demo/business-financial`     |
| `business-real-estate`   | `/theme-business-solutions-demo/business-real-estate`   |
| `business-education`     | `/theme-business-solutions-demo/business-education`     |
| `business-hospitality`   | `/theme-business-solutions-demo/business-hospitality`   |
| `business-consultancy`   | `/theme-business-solutions-demo/business-consultancy`   |
| `business-nonprofit`     | `/theme-business-solutions-demo/business-nonprofit`     |
| `business-manufacturing` | `/theme-business-solutions-demo/business-manufacturing` |
| `business-government`    | `/theme-business-solutions-demo/business-government`    |
| `business-recruiting`    | `/theme-business-solutions-demo/business-recruiting`    |
| `business-commerce`      | `/theme-business-solutions-demo/business-commerce`      |

## Verification

- Run `vendor/bin/pest packages/theme-business-solutions/tests --configuration=phpunit.xml`.
- In a disposable Capell app, install the core stack, Layout Builder, Foundation Theme, Demo Kit, and `capell-app/theme-business-solutions`.
- Generate demo content with Demo Kit before browser capture.
- Open each screenshot target anonymously and scan the response for `capell-theme`, `data-capell-theme`, `theme-business-solutions`, `signed`, `filament`, `editor`, and `/admin`.
- Capture every entry listed in [screenshots.json](screenshots.json).

## Package Manifest

- Composer name: `capell-app/theme-business-solutions`
- Primary theme key: `business-legal`
- Product group: Capell Themes
- Kind: theme
- Tier: premium
- Bundle: themes
- Contexts: `frontend`
- Requires: `capell-app/core`, `capell-app/foundation-theme`
- Demo screenshot dependency: `capell-app/demo-kit`

## Admin Surfaces

- Core Themes resource: `ThemeResource:index`. This is a core/admin surface that lists installed business themes.
- Layout Builder content editor. This is the admin surface used to show unique page content being edited as blocks.

## Commands

- The package itself does not register a demo command.
- Demo content for screenshot runs is generated by `capell-app/demo-kit` in the disposable host app.

## Routes And Config

- No public routes are registered by this package.
- Screenshot routes are disposable harness routes only.

## Migrations

- None.

## Pitfalls

- Install Layout Builder before Foundation Theme in the disposable screenshot harness.
- Install Demo Kit before screenshot capture so pages contain meaningful block content.
- Keep active theme settings aligned with the theme key under capture.
- Do not store designed Tailwind or section wrapper markup in CMS content fields.
- Do not publish screenshots from blank pages; they will only prove the shell renders, not that unique content remains editable.

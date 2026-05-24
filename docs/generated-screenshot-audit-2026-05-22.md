# Generated Screenshot Audit - 2026-05-22

## Scope

Reviewed committed generated screenshots under `public/docs/screenshots/packages` against the package screenshot contracts in `packages/*/docs/screenshots.json`.

This audit checks more than file presence:

- Screenshot manifest coverage and stale outputs.
- Whether CSS/admin styling appears loaded and correctly scoped.
- Whether the requested target screen is captured instead of a dashboard, placeholder, theme page, or unrelated surface.
- Whether required form fields and layout/content controls are visible when the manifest or source requires them.
- Whether frontend screenshots show seeded package content instead of the generic demo shell.

## Summary

The generated screenshot set is not correct and should not be published as package documentation.

The manifests themselves validate structurally with `node scripts/validate-screenshot-manifests.js`, but the generated output directory is stale, incomplete, and visually unreliable. There are 346 generated image files, 233 manifest entries with expected `screenshotPath` values, 140 missing expected files, and 99 stale non-dark output files that no longer match current manifest paths.

## Blockers

### 1. Content Blocks admin screenshots have catastrophic CSS/layout failure

Example: `public/docs/screenshots/packages/content-blocks/content-blocks-admin-create.png`

Observed:

- The capture is a very tall page dominated by huge unstyled sidebar icons.
- Form content is pushed far down the page and is not usable as documentation.
- Many admin captures are 11,675-12,798px tall, which is not consistent with normal Filament admin screens.

Affected examples:

- `content-blocks-admin-accordion.png`
- `content-blocks-admin-assets.png`
- `content-blocks-admin-call-to-action.png`
- `content-blocks-admin-create.png`
- `content-blocks-admin-hero.png`
- `content-blocks-admin-pricing.png`
- `content-blocks-admin-tabs.png`
- `content-blocks-admin-timeline.png`

Expected:

- A correctly styled admin shell with sidebar, header, form/table content, and block-specific fields visible in the viewport or normal full-page capture.

Assessment:

- These are failed screenshots. They show CSS or viewport styling broken during capture.

### 2. Current layout-builder manifest screenshots are missing

The current `packages/layout-builder/docs/screenshots.json` requires:

- `blocks-admin-index.png`
- `create-edit-block-form.png`
- `block-layouts-relation-manager.png`
- `layouts-admin-index.png`
- `page-layout-content-first-editor.png`
- `page-hero-editor.png`
- `public-main-content-render.png`
- `public-layout-area-render.png`
- `layout-health-widget.png`
- `recent-activity-widget.png`

None of those files exist in `public/docs/screenshots/packages/layout-builder`.

The existing files are stale names:

- `layout-builder-screen.png`
- `create-edit-widget-form.png`
- `widgets-admin-index.png`
- `sections-admin-index.png`
- `frontend-page-rendering-layout-builder-widgets.png`

Observed target failure:

- `public/docs/screenshots/packages/layout-builder/create-edit-widget-form.png` renders the dashboard, not a create/edit block or widget form.

Expected from source:

- `packages/layout-builder/src/Filament/Resources/Blocks/Schemas/BlockForm.php` resolves a block configurator and should show block-specific form controls.
- `packages/layout-builder/src/Filament/Resources/Pages/Schemas/Extenders/PageSchemaExtender.php` injects the Layout tab when layout editing is enabled.

Assessment:

- Layout Builder screenshots are not valid. The current required layout field/editor captures are absent.

### 3. Blog create/edit article form is the wrong screen

Example: `public/docs/screenshots/packages/blog/create-edit-article-form.png`

Observed:

- The image renders the dashboard.
- It does not show an article form.
- It does not show the layout field.
- It does not show article translation/content fields, tags, publish controls, or media fields.

Expected from `packages/blog/src/Filament/Configurators/Articles/ArticlePageConfigurator.php`:

- `SiteSelect::make()`
- `LayoutSelect::make('layout_id')`
- `TagsInput::make('tags')`
- `PublishSchema::make($configurator)`
- Translation/content form schema
- Settings schema and publish section on edit

Assessment:

- Failed target resolution. This screenshot cannot be used as evidence that the article form or layout field renders correctly.

### 4. Many frontend screenshots show a generic Demo placeholder, not package output

Examples:

- `public/docs/screenshots/packages/blog/blog-page-frontend-output.png`
- `public/docs/screenshots/packages/blog/archive-page-frontend-output.png`
- `public/docs/screenshots/packages/blog/tag-page-frontend-output.png`
- `public/docs/screenshots/packages/campaign-studio/frontend-landing-page-with-campaign-widgets.png`
- `public/docs/screenshots/packages/form-builder/frontend-form-output.png`
- `public/docs/screenshots/packages/foundation-theme/frontend-page-using-the-foundation-theme.png`
- `public/docs/screenshots/packages/insights/frontend-page-with-tracker-active.png`
- `public/docs/screenshots/packages/navigation/frontend-menu-output.png`
- `public/docs/screenshots/packages/publishing-studio/live-preview.png`
- `public/docs/screenshots/packages/search/frontend-search-results-page.png`
- `public/docs/screenshots/packages/theme-agency/frontend-page-rendered-with-agency-theme.png`
- `public/docs/screenshots/packages/theme-corporate/frontend-page-rendered-with-corporate-theme.png`
- `public/docs/screenshots/packages/theme-saas/frontend-page-rendered-with-saas-theme.png`

Observed:

- These are visually near-identical placeholder pages with small "Demo" content.
- They do not show seeded blog articles, campaign blocks, forms, navigation menus, search results, theme presentation, or package-specific frontend output.

Expected:

- Public package surfaces with seeded package content and no admin/editor metadata.

Assessment:

- Failed data seeding or URL resolution. These are not valid frontend package screenshots.

### 5. Admin screenshots often capture a generic dashboard or unrelated admin page

Examples:

- `public/docs/screenshots/packages/layout-builder/create-edit-widget-form.png` captures Dashboard.
- `public/docs/screenshots/packages/blog/create-edit-article-form.png` captures Dashboard.
- Several publishing-studio outputs capture Dashboard or generic Pages/Content Scheduler views regardless of the manifest use case.
- Login Audit widget output captures a general dashboard, not a login-audit-specific widget state.

Expected:

- The screenshot runner must resolve each manifest entry to the exact resource, relation manager, page, widget, modal, tab, or form state requested.

Assessment:

- The runner likely falls back to a default admin URL after target resolution fails.

## Manifest Coverage Failures

Missing expected files by package:

| Package             | Missing expected screenshots |
| ------------------- | ---------------------------: |
| newsletter          |                           13 |
| events              |                           11 |
| access-gate         |                           10 |
| layout-builder      |                           10 |
| public-actions      |                            8 |
| html-cache          |                            7 |
| seo-suite           |                            6 |
| blog                |                            5 |
| content-sections    |                            5 |
| site-discovery      |                            5 |
| translation-manager |                            5 |
| api                 |                            4 |
| document-lifecycle  |                            4 |
| notes               |                            4 |
| welcome-tour        |                            4 |
| block-library       |                            3 |
| dashboard-reports   |                            3 |
| demo-kit            |                            3 |
| frontend-authoring  |                            3 |
| ga4-reports         |                            3 |
| password-policy     |                            3 |
| wordpress-importer  |                            3 |
| campaign-studio     |                            2 |
| frontend-optimizer  |                            2 |
| hero                |                            2 |
| login-audit         |                            2 |
| publishing-studio   |                            2 |
| deployments         |                            1 |
| diagnostics         |                            1 |
| foundation-theme    |                            1 |
| media-ai            |                            1 |
| migration-assistant |                            1 |
| theme-agency        |                            1 |
| theme-corporate     |                            1 |
| theme-saas          |                            1 |

## Package-Level Assessment

| Package output present | Assessment                                                                                                                                                                                                                                                                              |
| ---------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| address                | Partially valid. Admin index/form screenshots render with Filament styling, but tables are mostly empty and should be reseeded before final documentation.                                                                                                                              |
| admin-preview          | Invalid. Captures show a generic frontend/theme placeholder with a large arc, not preview panel workflows.                                                                                                                                                                              |
| agent-bridge           | Invalid/partial. Most captures show the same prompt builder page; server health, token setup, audit entry, and confirmation flows are not distinguishable.                                                                                                                              |
| ai-orchestrator        | Invalid/partial. Captures show Extensions/Layout pages or placeholders, not the declared approval/capability/layout-preview workflows.                                                                                                                                                  |
| block-library          | Stale. Existing 38 outputs are mostly visually coherent, but current manifest expects only 3 different diagnostic/consuming-package screenshots.                                                                                                                                        |
| blog                   | Invalid. Admin index is plausible, but create/edit form is Dashboard and frontend pages are generic Demo placeholders. Required article form and layout field are not shown.                                                                                                            |
| campaign-studio        | Partial. Campaign group/landing page/form screens are styled, but indexes are empty and frontend campaign block output is a Demo placeholder. Current manifest expects `campaign-dashboard-blocks` and `frontend-landing-page-with-campaign-blocks`, while stale `widgets` names exist. |
| content-blocks         | Invalid. Admin captures have severe CSS/layout failure; frontend block previews are coherent but this output set is stale relative to current `block-library` manifest ownership.                                                                                                       |
| deployments            | Partial. Connection page appears styled, but only one of the expected outputs is present.                                                                                                                                                                                               |
| diagnostics            | Mostly plausible. Site health/dashboard pages render with admin styling, but one expected command palette screenshot is missing.                                                                                                                                                        |
| form-builder           | Invalid/partial. Admin screens show form mappings, but frontend form output is a Demo placeholder and expected form builder schema/submission screens need direct verification.                                                                                                         |
| foundation-theme       | Invalid/partial. Settings screen renders, but frontend/theme screenshots are placeholders or large arc artifacts rather than a styled Foundation theme page/header area.                                                                                                                |
| frontend-authoring     | Invalid. Existing images do not include the required public homepage with post-load authoring regions. The admin settings screenshots do not prove the beacon/admin overlay workflow.                                                                                                   |
| html-optimizer         | Invalid. Captures show raw/simple frontend content or placeholder arc output, not before/after HTML inspection or middleware registration proof.                                                                                                                                        |
| insights               | Invalid/partial. Settings screen is styled, but frontend tracker and overview/widget screenshots are empty or generic dashboard-like captures.                                                                                                                                          |
| layout-builder         | Invalid. Current required files are missing; stale outputs include wrong target screens. Layout/editor fields are not proven.                                                                                                                                                           |
| login-audit            | Partial. Settings and authentication log index render, but data/widgets are empty or generic. Missing user access summary and relation manager outputs.                                                                                                                                 |
| media-library          | Partial. Media admin pages render but are empty; media-ai expected screenshot is missing.                                                                                                                                                                                               |
| migration-assistant    | Invalid/partial. Outputs repeatedly show Import Batches, not the distinct validation, relation resolution, export intent, rollback, and recovery screens.                                                                                                                               |
| navigation             | Partial. Navigation admin/create screens render; frontend menu output is Demo placeholder and page/site relation captures need target verification.                                                                                                                                     |
| publishing-studio      | Invalid/partial. Some workflow/admin pages render, but several outputs are dashboard/placeholders and current manifest still misses responsibility screenshots.                                                                                                                         |
| redirects              | Partial. Admin screens render, but generated outputs are stale relative to manifest and need seeded data/import/export state verification.                                                                                                                                              |
| search                 | Invalid/partial. Settings screen renders; frontend search results are Demo placeholder; widgets are empty/generic.                                                                                                                                                                      |
| seo-suite              | Partial. Some admin pages render with styling, but several current manifest outputs are missing and generated pages look generic/repeated.                                                                                                                                              |
| tags                   | Partial. Tag admin/form screens render; relation manager/page form context should be verified with seeded tagged records.                                                                                                                                                               |
| theme-agency           | Invalid/partial. Theme admin list renders; frontend theme page is placeholder/arc output.                                                                                                                                                                                               |
| theme-corporate        | Invalid/partial. Theme admin list renders; frontend theme page is placeholder/arc output.                                                                                                                                                                                               |
| theme-saas             | Invalid/partial. Theme admin list renders; frontend theme page is placeholder/arc output.                                                                                                                                                                                               |

## Required Follow-Up Checks Before Regenerating

The screenshot runner should fail a screenshot entry when any of these checks fail:

1. Requested file path matches the current manifest `screenshotPath`.
2. Target URL/resource/page resolves to the intended screen, not Dashboard.
3. Admin CSS is loaded:
    - No giant raw sidebar icons.
    - Sidebar/header dimensions match Filament shell.
    - Forms/tables render as styled controls, not raw HTML.
4. Required form fields are visible for create/edit form captures:
    - Blog article create/edit must show site, layout, tags, publish controls, translation/content/settings fields.
    - Campaign page extension must show campaign group, landing page toggle, primary goal, UTM content, and UTM term when capturing page campaign settings.
    - Layout Builder block form must show resolved component/display/settings/translations/asset tabs.
    - Layout Builder page editor must show the Layout tab/content-first editor when layout editing is enabled.
5. Required frontend content is visible:
    - Blog screenshots must show article lists/archive/tag output.
    - Campaign frontend screenshot must show campaign hero/CTA/form blocks.
    - Form Builder frontend screenshot must show a rendered form.
    - Navigation frontend screenshot must show a rendered menu.
    - Theme screenshots must show the active theme page, not placeholder arc output.
6. Seeded data is present for table/widget screenshots:
    - Empty tables are acceptable only when the manifest explicitly asks for an empty state.
    - Dashboard widgets must show package-specific widget state, not generic dashboard fallback.
7. Browser console/network capture should be retained for each run:
    - CSS/JS 404s fail the run.
    - Livewire/Filament request failures fail the run.
    - Redirect to login/dashboard when a resource was requested fails the run.

## Commands Run

```bash
node scripts/validate-screenshot-manifests.js
find public/docs/screenshots/packages -type f \( -iname '*.png' -o -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.webp' \) | sort | wc -l
```

Additional local image checks were run with ImageMagick to identify dimensions, very low visual variance, tiny files, and repeated thumbnail signatures.

## Review Artifacts

Temporary contact sheets were generated at:

```text
/tmp/capell-screenshot-review/sheets
```

These sheets group screenshots by package for faster visual review.

## Detailed Follow-Ups

The full entry-by-entry matrix is in `docs/generated-screenshot-entry-audit-2026-05-22.md`.

The package recapture and preview checklist is in `docs/generated-screenshot-recapture-plan-2026-05-22.md`.

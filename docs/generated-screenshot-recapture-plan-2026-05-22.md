# Generated Screenshot Recapture Plan - 2026-05-22

## Goal

Every package and theme screenshot must be regenerated from a real installed Capell app state, then checked against the current `packages/*/docs/screenshots.json` manifest entry. A screenshot is not accepted just because it exists or is nonblank.

## Acceptance Gates

Every manifest entry must pass these gates before the screenshot is approved:

1. The output filename exactly matches the current manifest `screenshotPath`.
2. The browser is on the intended resource/page/route/widget/modal, not the dashboard or a fallback page.
3. Filament/admin CSS is loaded, with normal sidebar/header sizing and styled form/table controls.
4. Required seeded data is present unless the manifest explicitly asks for an empty state.
5. Create/edit form screenshots show the required fields, including package-injected fields.
6. Layout Builder screenshots show the layout field, layout tab, content-first editor, block form tabs, or rendered layout area required by the manifest.
7. Frontend screenshots show package-specific public output and no admin/editor metadata.
8. Theme screenshots show the actual theme renderer sections: navigation, hero, features, proof, content listing, CTA, and footer where the theme declares those sections.
9. Browser console errors, CSS/JS 404s, Livewire request failures, and redirects to dashboard/login fail the capture.

## Runner Findings

The live screenshot runner exists at `../capell-screenshot-runner`. A dry run against this packages repo expanded the manifests to 466 light/dark capture entries:

```bash
CAPELL_PACKAGES_REPO=/Users/ben/Sites/packages/capell/capell-packages-4 npm run validate -- --repo /Users/ben/Sites/packages/capell/capell-packages-4
```

Result:

- Captured: 0
- Skipped: 466
- Failed: 0

That dry run only proves the manifests can be expanded. It does not prove target correctness, CSS health, field visibility, seeded data, or frontend output quality.

The runner needs stricter validation before the next full capture:

- Add defaults for current resource targets such as `BlockResource`, `ArticleResource`, current `ThemeResource:index` entries, and all renamed manifest IDs.
- Fail if an admin capture lands on Dashboard when the manifest target is a resource/form/widget/modal.
- Fail if a `waitFor: form` entry does not expose expected labels/fields from the manifest or source.
- Fail if Filament sidebar SVG/icons render at raw unstyled dimensions.
- Fail if frontend screenshots contain only the generic Demo placeholder when package-specific content is expected.
- Fail if the output path differs from the manifest `screenshotPath`.
- Record console errors, failed network requests, final URL, page title, and matched selectors for every screenshot.

## Current Package Status

`Fail` means a present screenshot is known wrong. `Missing` means the current manifest path is absent. `Recapture` means the file exists but the required seeded state is not evidenced. `Review` means the file exists and is plausible, but still needs browser assertions before approval.

| Package             | Entries | Fail | Missing | Recapture | Review | Notes                                                                                             |
| ------------------- | ------: | ---: | ------: | --------: | -----: | ------------------------------------------------------------------------------------------------- |
| access-gate         |      10 |    0 |      10 |         0 |      0 | Full package capture missing.                                                                     |
| address             |       5 |    0 |       0 |         3 |      2 | Styled admin shell exists, but seeded countries/addresses/site settings state must be proven.     |
| agent-bridge        |       5 |    0 |       0 |         1 |      4 | Existing screens need target/state assertions; several look like the same prompt builder surface. |
| ai-orchestrator     |       3 |    0 |       0 |         0 |      3 | Optional integration states need consuming-package proof.                                         |
| api                 |       4 |    0 |       4 |         0 |      0 | Full API route capture missing.                                                                   |
| block-library       |       3 |    0 |       3 |         0 |      0 | Current manifest entries missing; old block-library gallery screenshots are stale.                |
| blog                |      10 |    4 |       5 |         1 |      0 | Article form is Dashboard; frontend outputs are Demo placeholders; widgets/block outputs missing. |
| campaign-studio     |       6 |    0 |       2 |         4 |      0 | Admin forms need seeded state; dashboard and frontend campaign block outputs missing.             |
| content-sections    |       5 |    0 |       5 |         0 |      0 | Full package capture missing.                                                                     |
| dashboard-reports   |       3 |    0 |       3 |         0 |      0 | Dashboard widgets/settings missing.                                                               |
| demo-kit            |       3 |    0 |       3 |         0 |      0 | Full package capture missing.                                                                     |
| deployments         |       2 |    0 |       1 |         0 |      1 | Connection page exists; widget missing.                                                           |
| diagnostics         |       6 |    0 |       1 |         0 |      5 | Admin pages plausible; command palette missing and widget/page data needs browser proof.          |
| document-lifecycle  |       4 |    0 |       4 |         0 |      0 | Full package capture missing.                                                                     |
| email-studio        |       0 |    0 |       0 |         0 |      0 | Manifest intentionally has no visible entries.                                                    |
| events              |      11 |    0 |      11 |         0 |      0 | Full package capture missing.                                                                     |
| form-builder        |       5 |    1 |       0 |         0 |      4 | Frontend form output is Demo placeholder; admin screens need form/schema/submission assertions.   |
| foundation-theme    |       4 |    1 |       1 |         0 |      2 | Frontend page is placeholder; header layout area missing; settings/assets need browser proof.     |
| frontend-authoring  |       5 |    0 |       3 |         0 |      2 | Admin config images exist; public authoring-enabled desktop/mobile captures missing.              |
| frontend-optimizer  |       2 |    0 |       2 |         0 |      0 | Full package capture missing.                                                                     |
| ga4-reports         |       3 |    0 |       3 |         0 |      0 | Full package capture missing.                                                                     |
| hero                |       2 |    0 |       2 |         0 |      0 | Full package capture missing.                                                                     |
| html-cache          |       7 |    0 |       7 |         0 |      0 | Current manifest outputs missing; existing html-optimizer screenshots are stale.                  |
| insights            |       5 |    1 |       0 |         0 |      4 | Frontend tracker output is placeholder; widgets/settings need seeded data proof.                  |
| layout-builder      |      10 |    0 |      10 |         0 |      0 | Current manifest outputs missing. Stale files do not show layout field/editor requirements.       |
| login-audit         |       6 |    0 |       2 |         0 |      4 | Settings/logs plausible; user access summary/relation manager missing.                            |
| media-ai            |       1 |    0 |       1 |         0 |      0 | Optional/blocked screenshot missing.                                                              |
| media-library       |       4 |    0 |       0 |         0 |      4 | Admin pages exist but empty; seed media health/migration/curator field states.                    |
| migration-assistant |       6 |    0 |       1 |         0 |      5 | Existing screens appear repetitive; relation/export/rollback states need proof.                   |
| navigation          |       5 |    1 |       0 |         0 |      4 | Frontend menu output is Demo placeholder; admin/page relation states need assertions.             |
| newsletter          |      13 |    0 |      13 |         0 |      0 | Full package capture missing.                                                                     |
| notes               |       4 |    0 |       4 |         0 |      0 | Full package capture missing.                                                                     |
| password-policy     |       3 |    0 |       3 |         0 |      0 | Full package capture missing.                                                                     |
| public-actions      |       8 |    0 |       8 |         0 |      0 | Full package capture missing.                                                                     |
| publishing-studio   |      13 |    2 |       2 |         0 |      9 | Live preview/preview banner are placeholders; responsibility screenshots missing.                 |
| search              |       6 |    2 |       0 |         0 |      4 | Frontend search output is placeholder; widgets/settings need seeded data proof.                   |
| seo-suite           |      11 |    0 |       6 |         0 |      5 | Many current outputs missing; existing pages need distinct target verification.                   |
| site-discovery      |       5 |    0 |       5 |         0 |      0 | Full package capture missing.                                                                     |
| tags                |       4 |    0 |       0 |         0 |      4 | Admin screens exist; relation/page form state needs seeded tagged records.                        |
| theme-agency        |       3 |    1 |       1 |         0 |      1 | Frontend page is placeholder/arc output; admin list/preview need theme record proof.              |
| theme-corporate     |       3 |    1 |       1 |         0 |      1 | Frontend page is placeholder/arc output; admin list/preview need theme record proof.              |
| theme-saas          |       3 |    1 |       1 |         0 |      1 | Frontend page is placeholder/arc output; admin list/preview need theme record proof.              |
| translation-manager |       5 |    0 |       5 |         0 |      0 | Full package capture missing.                                                                     |
| welcome-tour        |       4 |    0 |       4 |         0 |      0 | Full package capture missing.                                                                     |
| wordpress-importer  |       3 |    0 |       3 |         0 |      0 | Full package capture missing.                                                                     |

## Theme-Specific Preview Requirements

The theme packages register section renderers in code, so the screenshot runner must verify those sections visually rather than only checking a theme row exists.

### foundation-theme

Required previews:

- Settings screen with Foundation Theme fields visible.
- Public page rendered through Foundation Theme with header, footer, tokens, and components.
- Generated Tailwind asset output review with actual generated asset evidence.
- Header Layout Builder area with a seeded header-area block.

Current issue:

- The public page is a generic placeholder.
- The header-area screenshot is missing.

### theme-agency

Required previews:

- Themes admin list showing an Agency theme record.
- `/theme-agency-demo` rendered through Agency navigation, hero, features, proof, content listing, CTA, and footer.
- Signed preview URL output for the Agency theme.

Current issue:

- Frontend output is a placeholder/arc image, not the Agency renderer.
- The current manifest `theme-admin-list-showing-agency.png` is missing; stale `theme-preset-selection-showing-agency.png` exists instead.

### theme-corporate

Required previews:

- Themes admin list showing a Corporate theme record.
- `/theme-corporate-demo` rendered through Corporate navigation, hero, features, proof, content listing, CTA, and footer.
- Signed preview URL output for the Corporate theme.

Current issue:

- Frontend output is a placeholder/arc image, not the Corporate renderer.
- The current manifest `theme-admin-list-showing-corporate.png` is missing; stale `theme-preset-selection-showing-corporate.png` exists instead.

### theme-saas

Required previews:

- Themes admin list showing a SaaS theme record.
- `/theme-saas-demo` rendered through SaaS navigation, hero, features, proof, content listing, CTA, and footer.
- Signed preview URL output for the SaaS theme.

Current issue:

- Frontend output is a placeholder/arc image, not the SaaS renderer.
- The current manifest `theme-admin-list-showing-saas.png` is missing; stale `theme-preset-selection-showing-saas.png` exists instead.

## High-Risk Form/Layout Checks

These entries must be explicitly asserted in browser automation because the existing screenshots either show the wrong screen or do not prove the fields are present.

| Package          | Entry                                  | Required proof                                                                                                             |
| ---------------- | -------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| blog             | `create-edit-article-form`             | Article create/edit form with site, `layout_id`, tags, publish controls, translations/content, settings, and media fields. |
| layout-builder   | `create-edit-block-form`               | Block form with resolved configurator tabs for component, display, settings, translations, and assets.                     |
| layout-builder   | `page-layout-content-first-editor`     | Page edit screen with Layout tab/content-first layout editor visible and seeded editable block assets.                     |
| layout-builder   | `page-hero-editor`                     | Page edit screen with hero-specific fields injected by the package.                                                        |
| content-sections | `sections-create`                      | Blueprint selector visible and controlling available fields.                                                               |
| content-sections | `sections-edit-with-assets`            | Section translations and at least one related block asset/relation manager visible.                                        |
| campaign-studio  | `campaign-conversion-goals-form`       | Goal type, attribution model, active state, and seeded campaign group options.                                             |
| campaign-studio  | `cta-block-form`                       | Title, body, button/action, style, tracking, campaign group, and target URL/page fields.                                   |
| campaign-studio  | page campaign extension                | Campaign group, landing page toggle, primary goal, UTM content, and UTM term in page sidebar fields.                       |
| form-builder     | `create-edit-form-schema-screen`       | Form schema builder fields, submission handling settings, and styled controls.                                             |
| navigation       | `page-form-navigation-tab`             | Page form navigation tab with seeded navigation metadata controls.                                                         |
| tags             | `article-or-page-form-using-tagsinput` | Tags input rendered inside an article/page form with seeded tags.                                                          |

## Recapture Order

1. Fix runner correctness first: fail on target fallback, missing CSS, console errors, and missing manifest output path.
2. Recapture `layout-builder`, `blog`, `foundation-theme`, and `theme-*` first because they underpin the most visible docs and prove layout/theme rendering.
3. Recapture packages with all screenshots missing: `access-gate`, `events`, `newsletter`, `public-actions`, `html-cache`, `site-discovery`, `translation-manager`, `welcome-tour`, `wordpress-importer`, and related packages.
4. Recapture packages with placeholder frontend output: `blog`, `campaign-studio`, `form-builder`, `foundation-theme`, `insights`, `navigation`, `publishing-studio`, `search`, and all `theme-*` packages.
5. Remove stale screenshots after replacement so docs cannot accidentally reference outdated files.

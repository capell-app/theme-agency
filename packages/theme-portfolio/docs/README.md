# Theme Portfolio Docs

Theme Portfolio is the first-party Capell theme for creators, consultants,
independent studios, case studies, services, media kits, and newsletters.

## Read Next

- [Overview](overview.md)
- [Package README](../README.md)
- [Creating a Capell theme](../../../docs/creating-a-theme.md)
- [Theme Screenshot QA Playbook](../../../docs/theme-screenshot-qa-playbook.md)

## Developer Starting Points

| Need                                       | Start Here                                                                                |
| ------------------------------------------ | ----------------------------------------------------------------------------------------- |
| Theme definition and optional integrations | `src/PortfolioThemeServiceProvider.php`                                                   |
| Demo content install                       | `src/Console/Commands/DemoCommand.php`, `src/Actions/InstallPortfolioThemeDemoAction.php` |
| Optional integration flags                 | Core `ViewSectionRenderer` extra view data                                                |
| Theme management entry                     | `src/Manifest/ThemeManagementPageContribution.php`                                        |
| Health diagnostics                         | `src/Health/ThemePortfolioHealthCheck.php`                                                |
| Public output checks                       | `tests/Unit/PublicOutputSafetyTest.php`                                                   |

## Section Integrations

Portfolio-specific sections can use:

- Content Sections for the `case-studies` section.
- Media Library for the `work-grid` section.
- Newsletter for the `newsletter` section.

These integrations must stay optional so the theme can install before a host app
chooses its content, media, and audience-capture stack.

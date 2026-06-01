# Theme Nonprofit Docs

Theme Nonprofit is the first-party Capell theme for charities, civic
organisations, campaigns, donations, volunteering, and community stories.

## Read Next

- [Overview](overview.md)
- [Package README](../README.md)
- [Creating a Capell theme](../../../docs/creating-a-theme.md)
- [Theme Screenshot QA Playbook](../../../docs/theme-screenshot-qa-playbook.md)

## Developer Starting Points

| Need                                       | Start Here                                                                                |
| ------------------------------------------ | ----------------------------------------------------------------------------------------- |
| Theme definition and optional integrations | `src/NonprofitThemeServiceProvider.php`                                                   |
| Demo content install                       | `src/Console/Commands/DemoCommand.php`, `src/Actions/InstallNonprofitThemeDemoAction.php` |
| Optional integration flags                 | Core `ViewSectionRenderer` extra view data                                                |
| Theme management entry                     | `src/Manifest/ThemeManagementPageContribution.php`                                        |
| Health diagnostics                         | `src/Health/ThemeNonprofitHealthCheck.php`                                                |
| Public output checks                       | `tests/Unit/PublicOutputSafetyTest.php`                                                   |

## Section Integrations

Nonprofit-specific sections can use:

- Campaign Studio for the `campaigns` section.
- Form Builder for the `volunteer-donate` section.
- Events for the `events` section.
- Blog for the `stories` section.

These integrations must stay optional so the theme can install before a host app
chooses its campaign, donation, events, and storytelling stack.

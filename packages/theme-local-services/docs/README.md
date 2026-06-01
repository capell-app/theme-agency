# Theme Local Services Docs

Theme Local Services is the first-party Capell theme for trades, clinics,
consultancies, and local operators that need quote-led service pages.

## Read Next

- [Overview](overview.md)
- [Package README](../README.md)
- [Creating a Capell theme](../../../docs/creating-a-theme.md)
- [Theme Screenshot QA Playbook](../../../docs/theme-screenshot-qa-playbook.md)

## Developer Starting Points

| Need                                       | Start Here                                                                                    |
| ------------------------------------------ | --------------------------------------------------------------------------------------------- |
| Theme definition and optional integrations | `src/LocalServicesThemeServiceProvider.php`                                                   |
| Demo content install                       | `src/Console/Commands/DemoCommand.php`, `src/Actions/InstallLocalServicesThemeDemoAction.php` |
| Optional integration flags                 | Core `ViewSectionRenderer` extra view data                                                    |
| Theme management entry                     | `src/Manifest/ThemeManagementPageContribution.php`                                            |
| Health diagnostics                         | `src/Health/ThemeLocalServicesHealthCheck.php`                                                |
| Public output checks                       | `tests/Unit/PublicOutputSafetyTest.php`                                                       |

## Section Integrations

Local Services-specific sections can use:

- Form Builder for the `quote-form` section.
- Blog for the `resources` section.

These integrations must stay optional so the theme can install before a host app
decides which lead-capture and content packages it needs.

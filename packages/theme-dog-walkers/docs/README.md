# Theme Dog Walkers Docs

Theme Dog Walkers is the first-party Capell theme for dog walkers, sitters,
and neighbourhood pet-care teams that need trust-led enquiry pages.

## Read Next

- [Overview](overview.md)
- [Package README](../README.md)
- [Creating a Capell theme](../../../docs/creating-a-theme.md)
- [Package Screenshot Automation](../../../docs/package-screenshot-automation.md)

## Developer Starting Points

| Need                                       | Start Here                                                                                    |
| ------------------------------------------ | --------------------------------------------------------------------------------------------- |
| Theme definition and optional integrations | `src/DogWalkersThemeServiceProvider.php`                                                   |
| Demo content install                       | `src/Console/Commands/DemoCommand.php`, `src/Actions/InstallDogWalkersThemeDemoAction.php` |
| Optional integration flags                 | Core `ViewSectionRenderer` extra view data                                                    |
| Theme management entry                     | `src/Manifest/ThemeManagementPageContribution.php`                                            |
| Health diagnostics                         | `src/Health/ThemeDogWalkersHealthCheck.php`                                                |
| Public output checks                       | `tests/Unit/PublicOutputSafetyTest.php`                                                       |

## Section Integrations

Dog Walkers-specific sections can use:

- Form Builder for the `enquiry-form` section.
- Blog for the `resources` section.

These integrations must stay optional so the theme can install before a host app
decides which lead-capture and content packages it needs.

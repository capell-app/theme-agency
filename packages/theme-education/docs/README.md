# Theme Education Docs

Theme Education is the first-party Capell theme for course, school, training,
and learning-programme sites.

## Read Next

- [Overview](overview.md)
- [Package README](../README.md)
- [Creating a Capell theme](../../../docs/creating-a-theme.md)
- [Package Screenshot Automation](../../../docs/package-screenshot-automation.md)

## Developer Starting Points

| Need                                       | Start Here                                                                                |
| ------------------------------------------ | ----------------------------------------------------------------------------------------- |
| Theme definition and optional integrations | `src/EducationThemeServiceProvider.php`                                                   |
| Demo content install                       | `src/Console/Commands/DemoCommand.php`, `src/Actions/InstallEducationThemeDemoAction.php` |
| Optional integration flags                 | Core `ViewSectionRenderer` extra view data                                                |
| Theme management entry                     | `src/Manifest/ThemeManagementPageContribution.php`                                        |
| Health diagnostics                         | `src/Health/ThemeEducationHealthCheck.php`                                                |
| Public output checks                       | `tests/Unit/PublicOutputSafetyTest.php`                                                   |

## Section Integrations

Education-specific sections can use:

- Events for the `events` section.
- Form Builder for the `enrolment-cta` section.
- Blog for the `resources` section.

These integrations must stay optional. A host app should be able to install the
theme without installing every supported content package.

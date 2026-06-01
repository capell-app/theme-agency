# Theme Knowledge Docs

Theme Knowledge is the first-party Capell theme for knowledge bases, publishers,
resource hubs, and content-led teams.

## Read Next

- [Overview](overview.md)
- [Package README](../README.md)
- [Creating a Capell theme](../../../docs/creating-a-theme.md)
- [Theme Screenshot QA Playbook](../../../docs/theme-screenshot-qa-playbook.md)

## Developer Starting Points

| Need                                       | Start Here                                                                                |
| ------------------------------------------ | ----------------------------------------------------------------------------------------- |
| Theme definition and optional integrations | `src/KnowledgeThemeServiceProvider.php`                                                   |
| Demo content install                       | `src/Console/Commands/DemoCommand.php`, `src/Actions/InstallKnowledgeThemeDemoAction.php` |
| Optional integration flags                 | Core `ViewSectionRenderer` extra view data                                                |
| Theme management entry                     | `src/Manifest/ThemeManagementPageContribution.php`                                        |
| Health diagnostics                         | `src/Health/ThemeKnowledgeHealthCheck.php`                                                |
| Public output checks                       | `tests/Unit/PublicOutputSafetyTest.php`                                                   |

## Section Integrations

Knowledge-specific sections can use:

- Blog for the `resource-library` section.
- Search for the `search-listing` section.
- Newsletter for the `newsletter` section.

These integrations must stay optional so the theme can install in a Foundation
site before the full editorial stack is present.

# Theme Quiet Type Docs

A premium Capell theme with print-voice serif typography and a generous measure — for essayists and journals where the words are the design.

Start at the [package README](../README.md) when deciding whether to install this package. Use the docs below for setup, extension, debugging, and verification details.

## Guides

| Doc                                     | Use it for                                                                         |
| --------------------------------------- | ---------------------------------------------------------------------------------- |
| [Overview](overview.md)                 | Package boundary, runtime surfaces, install notes, and first troubleshooting path. |
| [Screenshot contract](screenshots.json) | Required admin and frontend captures for marketplace and documentation visibility. |

## Developer Starting Points

| Need                   | Start here                                                   |
| ---------------------- | ------------------------------------------------------------ |
| Theme service provider | `Capell\ThemeStudio\QuietType\QuietTypeThemeServiceProvider` |
| Demo content install   | `capell:theme-quiet-type-demo`                               |
| Theme management entry | `src/Manifest/ThemeManagementPageContribution.php`           |
| Health diagnostics     | `src/Health`                                                 |
| Public output checks   | `tests/Unit/PublicOutputSafetyTest.php`                      |

## Read Next

- [Package README](../README.md)
- [Creating a Capell theme](../../../docs/creating-a-theme.md)
- [Package Screenshot Automation](../../../docs/package-screenshot-automation.md)

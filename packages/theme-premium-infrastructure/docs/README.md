# Theme Premium Infrastructure Docs

A premium Capell theme for complex infrastructure platforms, B2B SaaS, developer tool companies, global fintech products, enterprise APIs, and trust-heavy product marketing.

Start at the [package README](../README.md) when deciding whether to install this package. Use the docs below for setup, extension, debugging, and verification details.

## Guides

| Doc                                     | Use it for                                                                         |
| --------------------------------------- | ---------------------------------------------------------------------------------- |
| [Overview](overview.md)                 | Package boundary, runtime surfaces, install notes, and first troubleshooting path. |
| [Screenshot contract](screenshots.json) | Required admin and frontend captures for marketplace and documentation visibility. |

## Developer Starting Points

| Need                   | Start here                                                                           |
| ---------------------- | ------------------------------------------------------------------------------------ |
| Theme service provider | `Capell\ThemeStudio\PremiumInfrastructure\PremiumInfrastructureThemeServiceProvider` |
| Demo content install   | `capell:theme-premium-infrastructure-demo`                                           |
| Theme management entry | `src/Manifest/ThemeManagementPageContribution.php`                                   |
| Health diagnostics     | `src/Health`                                                                         |
| Public output checks   | `tests/Unit/PublicOutputSafetyTest.php`                                              |

## Read Next

- [Package README](../README.md)
- [Creating a Capell theme](../../../docs/creating-a-theme.md)
- [Package Screenshot Automation](../../../docs/package-screenshot-automation.md)

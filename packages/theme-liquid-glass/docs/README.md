# Theme Liquid Glass Docs

Theme Liquid Glass documentation is intentionally small because the package is a
frontend renderer with no schema, routes, or admin resource of its own.

## Start Here

- [Overview](overview.md)
- [Credits and acknowledgements](credits-and-acknowledgements.md)
- [Screenshot contract](screenshots.json)
- [Root README](../README.md)
- [Marketplace assets](assets/marketplace/)

## Verification

Run focused tests from the repository root:

```bash
vendor/bin/pest packages/theme-liquid-glass/tests --configuration=phpunit.xml
```

Run public-output safety before changing Blade views:

```bash
vendor/bin/pest packages/theme-liquid-glass/tests/Unit/PublicOutputSafetyTest.php --configuration=phpunit.xml
```

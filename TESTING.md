# Testing

This monorepo uses [Pest](https://pestphp.com) 4.x with a parallel runner and pcov
for coverage. All packages are tested from the root.

## Quick start

```bash
COMPOSER=composer.local.json composer install
COMPOSER=composer.local.json composer test
```

## Test commands

Run Composer commands through the local overlay unless you are explicitly checking the public package manifest.

| Command                                                | Purpose                                                |
| ------------------------------------------------------ | ------------------------------------------------------ |
| `COMPOSER=composer.local.json composer test`           | Full suite (parallel)                                  |
| `COMPOSER=composer.local.json composer test:unit`      | Unit suite only                                        |
| `COMPOSER=composer.local.json composer test:group`     | One Pest group, controlled by `PEST_GROUP`             |
| `COMPOSER=composer.local.json composer coverage`       | Coverage run with a 90% minimum                        |
| `COMPOSER=composer.local.json composer coverage:blade` | Blade view coverage ratchet                            |
| `COMPOSER=composer.local.json composer preflight`      | Composer path check plus changed-file formatting       |
| `COMPOSER=composer.local.json composer preflight:all`  | Rector, Pint, Prettier, ESLint, PHPStan, audits, tests |

Run a single package:

```bash
vendor/bin/pest packages/theme-saas/tests --configuration=phpunit.xml
```

Run a single file:

```bash
vendor/bin/pest packages/theme-saas/tests/Unit/ExampleTest.php --configuration=phpunit.xml
```

## Test suites

Tests are collected from these directory patterns (configured in `phpunit.xml`):

| Suite            | Directories                                   |
| ---------------- | --------------------------------------------- |
| **Unit**         | `tests/*/Unit`, `packages/*/tests/Unit`       |
| **Feature**      | `tests/*/Feature`, `packages/*/tests/Feature` |
| **Architecture** | `tests/*/Arch`                                |
| **Integration**  | `tests/*/Integration`                         |

## Conventions

- **Framework**: Pest 4 function syntax (`test(...)`, `expect(...)`, `beforeAll(...)`)
- **No PHPUnit classes** — every test file uses top-level Pest functions only
- **Test actions directly**: `MyAction::run($input)`, not through HTTP unless testing an HTTP surface
- **Database tests**: use `Illuminate\Database\Capsule\Manager` with SQLite `:memory:` — no full
  Laravel app bootstrap required for unit tests
- **Mocking**: `Mockery::mock(InterfaceClass::class)` — no `$this->mock()` shorthand

## Coverage

Coverage is measured with pcov scoped to grouped package source directories:

```bash
COMPOSER=composer.local.json composer coverage
COMPOSER=composer.local.json composer coverage-report
```

The minimum threshold is **90%**. ServiceProviders, Console commands, and Middleware are excluded
from the measured source because they require an integration harness to test meaningfully.

## Pre-commit checks

The git hooks run these automatically before every commit:

1. Laravel Pint (code style)
2. Prettier (Blade / CSS / JS formatting)
3. ESLint

To run the full pre-flight suite manually:

```bash
COMPOSER=composer.local.json composer preflight
```

PHPStan runs at level 5. Annotate unavoidable suppressions with a comment explaining why.

## Adding tests for a new package

1. Create `packages/your-package/tests/Unit/` and place `*Test.php` files there.
2. The `phpunit.xml` source block includes flat `packages/*/src` package paths automatically; no config changes needed.
3. Follow the existing structure: one `*Test.php` per class under test, named after the class.
4. Add a `beforeAll` or `beforeEach` hook in the test file for any shared setup.

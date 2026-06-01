# Blade View Coverage

Capell package workspaces use [`capell-app/pest-plugin-blade-coverage`](https://github.com/capell-app/pest-plugin-blade-coverage) to check package Blade views separately from PHP line coverage. PHP coverage excludes `resources/views`, so this gate records views that Laravel actually renders during Pest runs.

The gate is ratcheted. Existing uncovered package views are stored in `tests/BladeCoverage/baseline.json` with content hashes. CI fails when a new package view is not rendered by tests, or when a baseline-uncovered view changes without gaining render coverage.

## Commands

Run the Blade view coverage gate:

```bash
composer coverage:blade
```

Refresh the baseline after intentionally accepting current uncovered views:

```bash
vendor/bin/pest --blade-coverage --blade-coverage-update-baseline --parallel --configuration=phpunit.xml
```

The config lives at `tests/blade-coverage.php` and targets:

```php
packages/*/resources/views/**/*.blade.php
```

## Expectations

- Prefer route, Livewire, or direct view tests that render the package Blade file.
- Includes, partials, and component views count when Laravel renders them.
- Source-only assertions with `file_get_contents()` do not count as coverage.
- Keep the baseline as the ratchet; do not add broad excludes for views that need focused tests.

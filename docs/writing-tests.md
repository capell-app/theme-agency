# Writing Tests

Capell package tests run through Composer's optimized PSR-4 autoloader. Any named class used by a test must live in a file path that matches its namespace.

## Test Fixtures

- Put reusable helper classes, fake models, fake commands, harness Livewire components, test policy users, recorders, and action probes in `tests/Fixtures` for package-local tests.
- Put root workspace fixtures in `tests/Packages/Fixtures`.
- Namespace fixture classes under the package test namespace, for example `Capell\LayoutBuilder\Tests\Fixtures\LayoutBuilderInstallRecorder`.
- Keep one named fixture class per file, with the filename matching the class name.
- Import fixtures into Pest files with a `use` statement.

Do not define named classes at the bottom of Pest files. Composer will discover those classes while scanning the PSR-4 test path, then skip them because the class name does not match the file path.

Anonymous classes are still fine for short one-off doubles when the class name is irrelevant and the object does not need to be reused.

## Commands

Use the narrowest useful Pest command while editing:

```bash
vendor/bin/pest packages/<package>/tests --configuration=phpunit.xml
```

After adding or moving fixture classes, refresh the optimized autoloader and confirm there are no PSR-4 warnings:

```bash
COMPOSER=composer.local.json composer dump-autoload --no-scripts
```

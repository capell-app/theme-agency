# Package Demo Audit Harness

Demo app path: `/Users/ben/Sites/packages/capell/capell-package-demo-audit`
Browser URL: `http://127.0.0.1:8000`
PHP runtime: Homebrew PHP 8.5.5 via PHP's built-in server.
Core baseline packages: `capell-app/core`, `capell-app/admin`, `capell-app/frontend`, `capell-app/installer`, `capell-app/marketplace`

Devilbox is present at `/Users/ben/devilbox`, but this harness currently uses the simpler built-in PHP server because no dedicated Devilbox vhost was configured during Task 1.

## Bootstrap Commands Used

Run from `/Users/ben/Sites/packages/capell`:

```bash
composer create-project laravel/laravel /Users/ben/Sites/packages/capell/capell-package-demo-audit
```

Then run from `/Users/ben/Sites/packages/capell/capell-package-demo-audit`:

```bash
composer require capell-app/core:4.x-dev capell-app/admin:4.x-dev capell-app/frontend:4.x-dev capell-app/installer:4.x-dev capell-app/marketplace:4.x-dev -W
php artisan filament:install --panels
php artisan capell:install --demo --package-mode=core --theme=none --url=http://127.0.0.1:8000 --name="Demo Admin" --email=admin@example.test --password=password --clear-cache --install-welcome-route --no-interaction
npm install
npm run build
```

The demo app `composer.json` is configured with local symlink path repositories:

```json
[
    {
        "type": "path",
        "url": "../capell-packages-4/packages/*",
        "options": {
            "symlink": true
        }
    },
    {
        "type": "path",
        "url": "../capell-4/packages/*",
        "options": {
            "symlink": true
        }
    },
    {
        "type": "vcs",
        "url": "https://github.com/fdemb/laravel-authentication-log"
    }
]
```

The `fdemb/laravel-authentication-log` repository is required while testing `capell-app/login-audit` on Laravel 13. Use the fork as a root app alias until [rappasoft/laravel-authentication-log#140](https://github.com/rappasoft/laravel-authentication-log/pull/140) is released upstream:

```bash
composer require 'rappasoft/laravel-authentication-log:dev-main as 6.0.1' capell-app/login-audit:4.x-dev -W
```

## Local Server

Start the app from `/Users/ben/Sites/packages/capell/capell-package-demo-audit`:

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

Verified URLs:

- Frontend: `http://127.0.0.1:8000`
- Admin login: `http://127.0.0.1:8000/admin/login`

## Reset Command

Use this command sequence to return the harness database to the core baseline before installing the next package:

```bash
cd /Users/ben/Sites/packages/capell/capell-package-demo-audit
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --graceful --ansi
php artisan capell:install --demo --package-mode=core --theme=none --url=http://127.0.0.1:8000 --name="Demo Admin" --email=admin@example.test --password=password --clear-cache --install-welcome-route --no-interaction
php artisan capell:frontend-install --no-interaction --ansi
npm run build
```

If an audited package has been added with Composer, remove it before the reset and return `composer.json` to only the five baseline Capell packages plus Laravel defaults.

Also remove that package's published config, generated Laravel migrations, and generated settings migrations before creating a fresh SQLite database. Published migration files remain in `database/migrations` after Composer removes a package, and they will otherwise be replayed into the next package's isolated baseline.

## Admin Account

- Email: `admin@example.test`
- Password: `password`
- Role: Capell admin role granted by `capell:install`

## Screenshot Output

Screenshots are written to:

```text
/Users/ben/Sites/packages/capell/capell-package-demo-audit/public/docs/screenshots/packages/{package}
```

Batch harnesses use the same path shape under their own app directory, for example:

```text
/Users/ben/Sites/packages/capell/capell-package-demo-audit-batch-5/public/docs/screenshots/packages/password-policy
```

For Filament admin screenshots, the harness must have a compiled panel theme at `public/build/filament/manifest.json`. If the page fails with `ViteManifestNotFoundException`, run `php artisan make:filament-theme admin --force --pm=npm`, set the Laravel Vite plugin `buildDirectory` to `build/filament`, then run `npm run build`.

## Current Health Notes

The app boots and returns HTTP 200 for both the public frontend and admin login under the built-in PHP server.

`php artisan capell:frontend-install --no-interaction --ansi` has published the core frontend runtime assets, so `public/vendor/capell-frontend/manifest.json` is now present.

`php artisan capell:doctor` still reports one frontend asset health warning:

- Missing generated Capell frontend Tailwind CSS.

Root cause: this baseline uses `--theme=none`, and `capell:frontend-after-install` resolves `capell.tailwind.generator`, which is registered by `capell-app/foundation-theme`. Keep admin-only package screenshots on the core-plus-package baseline. For frontend/theme packages, install the package's declared frontend/theme dependency stack before running Tailwind asset generation and visual capture.

## Login Audit Harness Notes

`capell-app/login-audit` is installed in this harness with the Laravel 13 Rappasoft fork alias documented above. The screenshot fixture also publishes the Login Audit migration and adds `Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable` to the disposable demo app `User` model so the user relation surface can be captured.

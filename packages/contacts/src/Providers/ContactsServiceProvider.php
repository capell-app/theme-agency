<?php

declare(strict_types=1);

namespace Capell\Contacts\Providers;

use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactActivity;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Models\Organisation;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Override;
use Spatie\LaravelPackageTools\Package;

final class ContactsServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-contacts';

    public static string $packageName = 'capell-app/contacts';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile('capell-contacts')
            ->hasTranslations()
            ->hasMigrations([
                '2026_05_31_000001_create_contacts_table',
                '2026_05_31_000002_create_contact_organisations_table',
                '2026_05_31_000003_create_contact_organisation_memberships_table',
                '2026_05_31_000004_create_contact_leads_table',
                '2026_05_31_000005_create_contact_activities_table',
                '2026_05_31_000006_add_source_identity_to_contacts_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->register(AdminServiceProvider::class);

        $this->app->booted(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            $this
                ->registerModels()
                ->registerProtectedTables();
        });
    }

    public function packageBooted(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        Relation::morphMap([
            'contact' => Contact::class,
            'contact_organisation' => Organisation::class,
            'contact_lead' => Lead::class,
            'contact_activity' => ContactActivity::class,
        ], merge: true);
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerModels(): self
    {
        CapellCore::registerModels([
            Contact::class,
            Organisation::class,
            Lead::class,
            ContactActivity::class,
        ]);

        return $this;
    }

    private function registerProtectedTables(): self
    {
        $tables = config('capell-contacts.tables', []);

        if (! is_array($tables)) {
            return $this;
        }

        foreach ($tables as $tableName) {
            if (! is_string($tableName)) {
                continue;
            }

            if ($tableName === '') {
                continue;
            }

            CapellCore::registerProtectedTable(static fn (): string => $tableName);
        }

        return $this;
    }
}

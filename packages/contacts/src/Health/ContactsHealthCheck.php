<?php

declare(strict_types=1);

namespace Capell\Contacts\Health;

use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactActivity;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Models\Organisation;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class ContactsHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var array<string, class-string<Model>>
     */
    private const array MODELS_BY_MORPH_ALIAS = [
        'contact' => Contact::class,
        'contact_organisation' => Organisation::class,
        'contact_lead' => Lead::class,
        'contact_activity' => ContactActivity::class,
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->storageTablesCheck(),
            $check->modelMorphAliasCheck(),
            $check->identityHashSecretCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts every privacy-sensitive storage table exists.
     */
    public function storageTablesCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables();

        return new DoctorCheckResultData(
            label: 'Contacts storage tables',
            passed: $missingTables === [],
            message: $missingTables === []
                ? 'All contacts, organisation, lead, and activity tables are present.'
                : 'Missing tables: ' . implode(', ', $missingTables) . '.',
            remediation: $missingTables === []
                ? null
                : 'Run the Capell migrations to create the contacts storage tables.',
        );
    }

    /**
     * Asserts the CRM models are discoverable through the morph map.
     */
    public function modelMorphAliasCheck(): DoctorCheckResultData
    {
        $unregisteredAliases = $this->unregisteredMorphAliases();

        return new DoctorCheckResultData(
            label: 'Contacts model morph aliases',
            passed: $unregisteredAliases === [],
            message: $unregisteredAliases === []
                ? 'Contact, organisation, lead, and activity models are registered in the morph map.'
                : 'Unregistered morph aliases: ' . implode(', ', $unregisteredAliases) . '.',
            remediation: $unregisteredAliases === []
                ? null
                : 'Ensure ContactsServiceProvider registers the contacts morph map.',
        );
    }

    /**
     * Asserts a secret is available for non-reversible identity hashes.
     */
    public function identityHashSecretCheck(): DoctorCheckResultData
    {
        $hasSecret = $this->hasIdentityHashSecret();

        return new DoctorCheckResultData(
            label: 'Contacts identity hash secret',
            passed: $hasSecret,
            message: $hasSecret
                ? 'An identity hash secret is configured for contact deduplication lookups.'
                : 'No identity hash secret is available; email, phone, and source lookups cannot be hashed.',
            remediation: $hasSecret
                ? null
                : 'Set capell-contacts.hash_secret or app.key so identities can be hashed for lookup.',
        );
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return array_values(collect($this->requiredTableNames())
            ->reject(static fn (string $tableName): bool => Schema::hasTable($tableName))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function unregisteredMorphAliases(): array
    {
        return array_values(collect(self::MODELS_BY_MORPH_ALIAS)
            ->reject(static fn (string $modelClass, string $morphAlias): bool => Relation::getMorphedModel($morphAlias) === $modelClass)
            ->keys()
            ->values()
            ->all());
    }

    public function hasIdentityHashSecret(): bool
    {
        $secret = config('capell-contacts.hash_secret') ?: config('app.key');

        return is_string($secret) && $secret !== '';
    }

    /**
     * @return list<string>
     */
    private function requiredTableNames(): array
    {
        $tables = config('capell-contacts.tables', []);

        $resolve = static function (string $key, string $fallback) use ($tables): string {
            $value = is_array($tables) ? ($tables[$key] ?? null) : null;

            return is_string($value) && $value !== '' ? $value : $fallback;
        };

        return [
            $resolve('contacts', 'contacts'),
            $resolve('organisations', 'contact_organisations'),
            $resolve('organisation_memberships', 'contact_organisation_memberships'),
            $resolve('leads', 'contact_leads'),
            $resolve('activities', 'contact_activities'),
        ];
    }
}

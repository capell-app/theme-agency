<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\PrivacyCenter\Models\ConsentPolicy;
use Capell\PrivacyCenter\Models\ConsentRecord;
use Capell\PrivacyCenter\Models\PolicyAcceptance;
use Capell\PrivacyCenter\Models\PrivacyRequest;
use Capell\PrivacyCenter\Models\RetentionRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class PrivacyCenterHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var array<string, class-string<Model>>
     */
    private const array MODELS_BY_MORPH_ALIAS = [
        'privacy_consent_policy' => ConsentPolicy::class,
        'privacy_consent_record' => ConsentRecord::class,
        'privacy_policy_acceptance' => PolicyAcceptance::class,
        'privacy_request' => PrivacyRequest::class,
        'privacy_retention_rule' => RetentionRule::class,
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
     * Asserts every privacy ledger storage table exists.
     */
    public function storageTablesCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables();

        return new DoctorCheckResultData(
            label: 'Privacy Center storage tables',
            passed: $missingTables === [],
            message: $missingTables === []
                ? 'All consent, policy acceptance, retention rule, and privacy request tables are present.'
                : 'Missing tables: ' . implode(', ', $missingTables) . '.',
            remediation: $missingTables === []
                ? null
                : 'Run the Capell migrations to create the Privacy Center storage tables.',
        );
    }

    /**
     * Asserts the privacy models are discoverable through the morph map.
     */
    public function modelMorphAliasCheck(): DoctorCheckResultData
    {
        $unregisteredAliases = $this->unregisteredMorphAliases();

        return new DoctorCheckResultData(
            label: 'Privacy Center model morph aliases',
            passed: $unregisteredAliases === [],
            message: $unregisteredAliases === []
                ? 'Consent policy, consent record, policy acceptance, privacy request, and retention rule models are registered in the morph map.'
                : 'Unregistered morph aliases: ' . implode(', ', $unregisteredAliases) . '.',
            remediation: $unregisteredAliases === []
                ? null
                : 'Ensure PrivacyCenterServiceProvider registers the Privacy Center morph map.',
        );
    }

    /**
     * Asserts a secret is available for non-reversible identity hashes.
     */
    public function identityHashSecretCheck(): DoctorCheckResultData
    {
        $hasSecret = $this->hasIdentityHashSecret();

        return new DoctorCheckResultData(
            label: 'Privacy Center identity hash secret',
            passed: $hasSecret,
            message: $hasSecret
                ? 'An identity hash secret is configured for consent evidence hashing.'
                : 'No identity hash secret is available; consent evidence cannot be hashed with a non-guessable salt.',
            remediation: $hasSecret
                ? null
                : 'Set capell-privacy-center.hash_secret (CAPELL_PRIVACY_CENTER_HASH_SECRET) or app.key so request evidence can be hashed.',
        );
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return collect($this->requiredTableNames())
            ->reject(static fn (string $tableName): bool => Schema::hasTable($tableName))
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function unregisteredMorphAliases(): array
    {
        return collect(self::MODELS_BY_MORPH_ALIAS)
            ->reject(static fn (string $modelClass, string $morphAlias): bool => Relation::getMorphedModel($morphAlias) === $modelClass)
            ->keys()
            ->values()
            ->all();
    }

    public function hasIdentityHashSecret(): bool
    {
        $secret = config('capell-privacy-center.hash_secret') ?: config('app.key');

        return is_string($secret) && $secret !== '';
    }

    /**
     * @return list<string>
     */
    private function requiredTableNames(): array
    {
        $tables = config('capell-privacy-center.tables', []);

        $resolve = static function (string $key, string $fallback) use ($tables): string {
            $value = is_array($tables) ? ($tables[$key] ?? null) : null;

            return is_string($value) && $value !== '' ? $value : $fallback;
        };

        return [
            $resolve('consent_policies', 'privacy_consent_policies'),
            $resolve('consent_records', 'privacy_consent_records'),
            $resolve('policy_acceptances', 'privacy_policy_acceptances'),
            $resolve('retention_rules', 'privacy_retention_rules'),
            $resolve('privacy_requests', 'privacy_requests'),
        ];
    }
}

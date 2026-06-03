<?php

declare(strict_types=1);

namespace Capell\PublicActions\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\PublicActions\Contracts\PublicActionDestinationAdapter;
use Capell\PublicActions\Listeners\SubmitPublicActionFromFormSubmission;
use Capell\PublicActions\Support\PublicActionDestinationAdapterRegistry;
use Capell\PublicActions\Support\PublicActionProviderPresetRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class PublicActionsHealthCheck implements ChecksExtensionHealth
{
    /**
     * Provider presets that must be normalised for the manifest claim to hold.
     *
     * @var list<string>
     */
    private const array EXPECTED_PRESET_KEYS = ['zapier', 'pipedream', 'n8n', 'make', 'generic'];

    /**
     * Configuration keys mapping to the package storage tables.
     *
     * @var array<string, string>
     */
    private const array STORAGE_TABLE_KEYS = [
        'actions' => 'public_actions',
        'destinations' => 'public_action_destinations',
        'submissions' => 'public_action_submissions',
        'dispatch_attempts' => 'public_action_dispatch_attempts',
        'integration_tokens' => 'public_action_integration_tokens',
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
            $check->webhookDispatchCheck(),
            $check->webhookSecurityCheck(),
            $check->providerPresetsCheck(),
            $check->formBuilderIntegrationCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts the dispatch storage tables exist and an HTTP webhook adapter is registered.
     */
    public function webhookDispatchCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables();
        $hasHttpAdapter = $this->hasHttpWebhookAdapter();
        $passed = $missingTables === [] && $hasHttpAdapter;

        return new DoctorCheckResultData(
            label: 'Webhook destinations dispatch JSON payloads with durable attempts',
            passed: $passed,
            message: $this->webhookDispatchMessage($missingTables, $hasHttpAdapter),
            remediation: $passed
                ? null
                : 'Run the Public Actions migrations and ensure the http_webhook destination adapter is registered.',
        );
    }

    /**
     * Asserts public webhook destinations cannot target insecure or private hosts by default.
     */
    public function webhookSecurityCheck(): DoctorCheckResultData
    {
        $allowsInsecure = (bool) config('capell-public-actions.allow_insecure_webhook_urls', false);
        $allowsPrivate = (bool) config('capell-public-actions.allow_private_webhook_urls', false);
        $passed = ! $allowsInsecure && ! $allowsPrivate;

        return new DoctorCheckResultData(
            label: 'Webhook destinations block private hosts and redact secrets',
            passed: $passed,
            message: $passed
                ? 'Webhook destinations reject plaintext and private-network endpoints.'
                : 'Webhook SSRF protection is relaxed: ' . $this->relaxedWebhookGuards($allowsInsecure, $allowsPrivate) . '.',
            remediation: $passed
                ? null
                : 'Set capell-public-actions.allow_insecure_webhook_urls and allow_private_webhook_urls to false on public sites.',
        );
    }

    /**
     * Asserts the advertised provider presets normalise to a dispatchable adapter.
     */
    public function providerPresetsCheck(): DoctorCheckResultData
    {
        $missingPresets = $this->missingPresetKeys();

        return new DoctorCheckResultData(
            label: 'Provider presets normalize Zapier, Pipedream, n8n, Make, and generic webhooks',
            passed: $missingPresets === [],
            message: $missingPresets === []
                ? 'Zapier, Pipedream, n8n, Make, and generic presets all resolve to a registered adapter.'
                : 'Missing or invalid provider presets: ' . implode(', ', $missingPresets) . '.',
            remediation: $missingPresets === []
                ? null
                : 'Restore the capell-public-actions.adapters.presets entries for the missing providers.',
        );
    }

    /**
     * Asserts the Form Builder bridge listener is available to feed submissions.
     */
    public function formBuilderIntegrationCheck(): DoctorCheckResultData
    {
        $hasListener = class_exists(SubmitPublicActionFromFormSubmission::class);
        $hasMappingConfig = is_array(config('capell-public-actions.form_builder.mappings'));
        $passed = $hasListener && $hasMappingConfig;

        return new DoctorCheckResultData(
            label: 'Form Builder submissions can feed Public Actions through package listeners',
            passed: $passed,
            message: $passed
                ? 'The Form Builder bridge listener and mapping configuration are available.'
                : 'The Form Builder bridge is unavailable: ' . $this->formBuilderGaps($hasListener, $hasMappingConfig) . '.',
            remediation: $passed
                ? null
                : 'Ensure the package config publishes form_builder.mappings and the bridge listener is autoloadable.',
        );
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return collect($this->storageTableNames())
            ->reject(static fn (string $tableName): bool => Schema::hasTable($tableName))
            ->values()
            ->all();
    }

    public function hasHttpWebhookAdapter(): bool
    {
        $adapter = resolve(PublicActionDestinationAdapterRegistry::class)->resolve('http_webhook');

        return $adapter instanceof PublicActionDestinationAdapter;
    }

    /**
     * @return list<string>
     */
    public function missingPresetKeys(): array
    {
        $registry = resolve(PublicActionProviderPresetRegistry::class);

        return collect(self::EXPECTED_PRESET_KEYS)
            ->reject(function (string $presetKey) use ($registry): bool {
                $preset = $registry->get($presetKey);

                return $preset !== null && $preset->adapter !== '';
            })
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function storageTableNames(): array
    {
        $tables = config('capell-public-actions.tables', []);

        return collect(self::STORAGE_TABLE_KEYS)
            ->map(static function (string $fallback, string $key) use ($tables): string {
                $value = is_array($tables) ? ($tables[$key] ?? null) : null;

                return is_string($value) && $value !== '' ? $value : $fallback;
            })
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $missingTables
     */
    private function webhookDispatchMessage(array $missingTables, bool $hasHttpAdapter): string
    {
        if ($missingTables !== []) {
            return 'Missing dispatch storage tables: ' . implode(', ', $missingTables) . '.';
        }

        if (! $hasHttpAdapter) {
            return 'The http_webhook destination adapter is not registered.';
        }

        return 'Dispatch storage tables and the http_webhook adapter are present for durable attempts.';
    }

    private function relaxedWebhookGuards(bool $allowsInsecure, bool $allowsPrivate): string
    {
        return collect([
            $allowsInsecure ? 'plaintext URLs are allowed' : null,
            $allowsPrivate ? 'private-network URLs are allowed' : null,
        ])
            ->filter()
            ->implode(' and ');
    }

    private function formBuilderGaps(bool $hasListener, bool $hasMappingConfig): string
    {
        return collect([
            $hasListener ? null : 'the bridge listener is missing',
            $hasMappingConfig ? null : 'the form_builder.mappings config is missing',
        ])
            ->filter()
            ->implode(' and ');
    }
}

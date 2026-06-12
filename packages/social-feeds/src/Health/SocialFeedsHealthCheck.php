<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\SocialFeeds\Blocks\SocialFeedBlockDefinitionProvider;
use Capell\SocialFeeds\Blocks\SocialFeedBlockRenderer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class SocialFeedsHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var list<string>
     */
    private const array REQUIRED_TABLES = [
        'social_feed_connections',
        'social_feed_items',
        'social_feed_oauth_states',
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
            $check->frontendWidgetCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    public function storageTablesCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables();

        return new DoctorCheckResultData(
            label: 'Social Feeds storage tables',
            passed: $missingTables === [],
            message: $missingTables === []
                ? 'The Social Feeds storage tables are present.'
                : 'Missing Social Feeds tables: ' . implode(', ', $missingTables) . '.',
            remediation: $missingTables === []
                ? null
                : 'Run the Capell migrations to create the Social Feeds connection, item, and OAuth state tables.',
        );
    }

    public function frontendWidgetCheck(): DoctorCheckResultData
    {
        $available = class_exists(SocialFeedBlockDefinitionProvider::class)
            && class_exists(SocialFeedBlockRenderer::class);

        return new DoctorCheckResultData(
            label: 'Social Feeds frontend widget',
            passed: $available,
            message: $available
                ? 'The Social Feeds block definition and renderer are available.'
                : 'The Social Feeds block definition or renderer could not be loaded.',
            remediation: $available
                ? null
                : 'Ensure the social-feeds package autoloader and service provider are registered.',
        );
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return array_values(array_filter(
            self::REQUIRED_TABLES,
            static fn (string $table): bool => ! Schema::hasTable($table),
        ));
    }
}

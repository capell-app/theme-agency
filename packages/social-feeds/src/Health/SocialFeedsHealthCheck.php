<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\SocialFeeds\Blocks\SocialFeedBlockDefinitionProvider;
use Capell\SocialFeeds\Blocks\SocialFeedBlockRenderer;
use Capell\SocialFeeds\Enums\SocialFeedConnectionStatus;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
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
            $check->staleSyncCheck(),
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

    public function staleSyncCheck(): DoctorCheckResultData
    {
        if (! Schema::hasTable('social_feed_connections')) {
            return new DoctorCheckResultData(
                label: 'Social Feeds stale syncs',
                passed: true,
                message: 'Social Feeds sync freshness cannot be checked until the connections table exists.',
                remediation: null,
            );
        }

        $staleMinutes = max(1, $this->integerConfig('capell-social-feeds.stale_sync_minutes', 180));
        $staleBefore = CarbonImmutable::now()->subMinutes($staleMinutes);
        $staleConnectionCount = SocialFeedConnection::query()
            ->where('status', SocialFeedConnectionStatus::Connected->value)
            ->where(function (Builder $query) use ($staleBefore): void {
                $query
                    ->whereNull('last_synced_at')
                    ->orWhere('last_synced_at', '<', $staleBefore);
            })
            ->count();

        return new DoctorCheckResultData(
            label: 'Social Feeds stale syncs',
            passed: $staleConnectionCount === 0,
            message: $staleConnectionCount === 0
                ? sprintf('All connected Social Feeds have synced within %d minutes.', $staleMinutes)
                : sprintf('%d connected Social Feed connection(s) have not synced within %d minutes.', $staleConnectionCount, $staleMinutes),
            remediation: $staleConnectionCount === 0
                ? null
                : 'Run capell:social-feeds:sync --all and confirm the scheduler or queue process is healthy.',
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

    private function integerConfig(string $key, int $default): int
    {
        $value = config($key);

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }
}

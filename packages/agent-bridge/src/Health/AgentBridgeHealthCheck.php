<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Health;

use Capell\AgentBridge\Settings\AgentBridgeSettings;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Laravel\Mcp\Facades\Mcp;
use Throwable;

final class AgentBridgeHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        return collect([
            self::checkMcpPackage(),
            self::checkTables(),
            self::checkCapabilityRegistry(),
            self::checkSettings(),
            self::checkRoutes(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()->every(
            fn (DoctorCheckResultData $check): bool => $check->passed,
        );
    }

    private static function checkMcpPackage(): DoctorCheckResultData
    {
        $available = class_exists(Mcp::class);

        return new DoctorCheckResultData(
            label: 'Laravel MCP package',
            passed: $available,
            message: $available
                ? 'The laravel/mcp package is installed and available.'
                : 'The laravel/mcp package is not installed.',
            remediation: $available ? null : 'Run: composer require laravel/mcp',
        );
    }

    private static function checkTables(): DoctorCheckResultData
    {
        $requiredTables = [
            'capell_agent_bridge_tokens',
            'capell_agent_bridge_confirmations',
            'capell_agent_bridge_audit_entries',
            'capell_agent_bridge_saved_prompts',
        ];

        try {
            $missingTables = collect($requiredTables)
                ->reject(fn (string $table): bool => Schema::hasTable($table));
        } catch (Throwable $throwable) {
            return new DoctorCheckResultData(
                label: 'Agent Bridge database tables',
                passed: false,
                message: 'Unable to check database tables.',
                remediation: $throwable->getMessage(),
            );
        }

        if ($missingTables->isNotEmpty()) {
            return new DoctorCheckResultData(
                label: 'Agent Bridge database tables',
                passed: false,
                message: 'Missing tables: ' . $missingTables->implode(', ') . '.',
                remediation: 'Run: php artisan migrate',
            );
        }

        return new DoctorCheckResultData(
            label: 'Agent Bridge database tables',
            passed: true,
            message: 'All required Agent Bridge tables are present.',
        );
    }

    private static function checkCapabilityRegistry(): DoctorCheckResultData
    {
        try {
            $registry = app(CapellAgentBridgeCapabilityRegistry::class);
            $capabilityCount = $registry->all()->count();
        } catch (Throwable $throwable) {
            return new DoctorCheckResultData(
                label: 'Agent Bridge capability registry',
                passed: false,
                message: 'Unable to resolve the capability registry.',
                remediation: $throwable->getMessage(),
            );
        }

        if ($capabilityCount === 0) {
            return new DoctorCheckResultData(
                label: 'Agent Bridge capability registry',
                passed: false,
                message: 'The capability registry is resolvable but contains no capabilities.',
                remediation: 'Ensure AgentBridgeServiceProvider is booted and registers built-in capabilities.',
            );
        }

        return new DoctorCheckResultData(
            label: 'Agent Bridge capability registry',
            passed: true,
            message: sprintf('Capability registry is resolvable with %d capability(ies) registered.', $capabilityCount),
        );
    }

    private static function checkSettings(): DoctorCheckResultData
    {
        try {
            $settings = app(AgentBridgeSettings::class);
            $bridgeEnabled = $settings->enable_user_resource_bridge;
        } catch (Throwable $throwable) {
            return new DoctorCheckResultData(
                label: 'Agent Bridge settings',
                passed: false,
                message: 'Unable to resolve Agent Bridge settings.',
                remediation: $throwable->getMessage(),
            );
        }

        return new DoctorCheckResultData(
            label: 'Agent Bridge settings',
            passed: true,
            message: sprintf(
                'Settings loaded. User resource bridge: %s.',
                $bridgeEnabled ? 'enabled' : 'disabled',
            ),
        );
    }

    private static function checkRoutes(): DoctorCheckResultData
    {
        $siteRoute = config('capell-agent-bridge.routes.site');
        $knowledgeRoute = config('capell-agent-bridge.routes.knowledge');

        $enabledServers = [];
        $disabledServers = [];

        if (is_string($siteRoute) && $siteRoute !== '') {
            $enabledServers[] = 'site';
        } else {
            $disabledServers[] = 'site';
        }

        if (is_string($knowledgeRoute) && $knowledgeRoute !== '') {
            $enabledServers[] = 'knowledge';
        } else {
            $disabledServers[] = 'knowledge';
        }

        if ($enabledServers === []) {
            return new DoctorCheckResultData(
                label: 'Agent Bridge MCP routes',
                passed: false,
                message: 'No MCP server routes are enabled. Both site and knowledge routes are disabled.',
                remediation: 'Enable at least one route in config/capell-agent-bridge.php under routes.',
            );
        }

        $message = 'Enabled MCP servers: ' . implode(', ', $enabledServers) . '.';

        if ($disabledServers !== []) {
            $message .= ' Disabled: ' . implode(', ', $disabledServers) . '.';
        }

        return new DoctorCheckResultData(
            label: 'Agent Bridge MCP routes',
            passed: true,
            message: $message,
        );
    }
}

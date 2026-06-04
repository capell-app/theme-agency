<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\SeoSuite\Actions\BuildPageSeoReportAction;
use Capell\SeoSuite\Actions\BuildRedirectOpportunityReportAction;
use Capell\SeoSuite\Actions\BuildRobotsTxtAction;
use Capell\SeoSuite\Actions\BuildSeoSuiteDoctorReportAction;
use Capell\SeoSuite\Actions\GenerateLlmsTxtAction;
use Capell\SeoSuite\Actions\SchemaGraphAction;
use Capell\SeoSuite\Contracts\SchemaTemplate;
use Capell\SeoSuite\Enums\SchemaTemplateTypeEnum;
use Capell\SeoSuite\Support\SchemaTemplates\SchemaTemplateRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class SeoSuiteHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var list<string>
     */
    private const array REQUIRED_TABLES = [
        'ai_creator_contexts',
        'ai_creator_sessions',
        'ai_discovery_crawler_rules',
        'ai_discovery_page_profiles',
        'ai_discovery_site_profiles',
        'ai_discovery_snapshots',
        'ai_generation_histories',
        'broken_links',
        'page_seo_snapshots',
        'page_speed_audit_results',
        'page_speed_audit_runs',
        'search_console_query_metrics',
        'search_console_url_metrics',
    ];

    /**
     * @var list<string>
     */
    private const array REQUIRED_AI_DISCOVERY_ROUTES = [
        'capell-frontend.llms-txt',
        'capell-frontend.llms-full-txt',
        'capell-frontend.robots-txt',
        'capell-frontend.page-markdown-home',
        'capell-frontend.page-markdown',
    ];

    /**
     * @var list<class-string>
     */
    private const array REQUIRED_SEO_SERVICES = [
        BuildPageSeoReportAction::class,
        BuildRedirectOpportunityReportAction::class,
        BuildRobotsTxtAction::class,
        BuildSeoSuiteDoctorReportAction::class,
        GenerateLlmsTxtAction::class,
        SchemaGraphAction::class,
        SchemaTemplateRegistry::class,
    ];

    /**
     * @var list<SchemaTemplateTypeEnum>
     */
    private const array REQUIRED_SCHEMA_TEMPLATES = [
        SchemaTemplateTypeEnum::WebPage,
        SchemaTemplateTypeEnum::Article,
    ];

    /**
     * @var list<string>
     */
    private const array REQUIRED_AI_CRAWLER_AGENTS = [
        'GPTBot',
        'ClaudeBot',
        'PerplexityBot',
        'Google-Extended',
        'CCBot',
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
            $check->aiDiscoveryRoutesCheck(),
            $check->seoServiceBindingsCheck(),
            $check->schemaTemplateRegistryCheck(),
            $check->aiCrawlerPolicyCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts the storage used by SEO reports, broken links, Search Console, PageSpeed, and AI Discovery exists.
     */
    public function storageTablesCheck(): DoctorCheckResultData
    {
        $missingTables = $this->missingTables();

        return new DoctorCheckResultData(
            label: __('capell-seo-suite::generic.health_storage_tables_label'),
            passed: $missingTables === [],
            message: $missingTables === []
                ? __('capell-seo-suite::generic.health_storage_tables_passed')
                : __('capell-seo-suite::generic.health_storage_tables_failed', ['tables' => implode(', ', $missingTables)]),
            remediation: $missingTables === []
                ? null
                : (string) __('capell-seo-suite::generic.health_storage_tables_remediation'),
        );
    }

    /**
     * Asserts generated AI Discovery endpoints are registered.
     */
    public function aiDiscoveryRoutesCheck(): DoctorCheckResultData
    {
        $missingRoutes = $this->missingAiDiscoveryRoutes();

        return new DoctorCheckResultData(
            label: __('capell-seo-suite::generic.health_ai_discovery_routes_label'),
            passed: $missingRoutes === [],
            message: $missingRoutes === []
                ? __('capell-seo-suite::generic.health_ai_discovery_routes_passed')
                : __('capell-seo-suite::generic.health_ai_discovery_routes_failed', ['routes' => implode(', ', $missingRoutes)]),
            remediation: $missingRoutes === []
                ? null
                : (string) __('capell-seo-suite::generic.health_ai_discovery_routes_remediation'),
        );
    }

    /**
     * Asserts the key actions behind manifest health surfaces are resolvable.
     */
    public function seoServiceBindingsCheck(): DoctorCheckResultData
    {
        $unresolvableServices = $this->unresolvableSeoServices();

        return new DoctorCheckResultData(
            label: __('capell-seo-suite::generic.health_seo_services_label'),
            passed: $unresolvableServices === [],
            message: $unresolvableServices === []
                ? __('capell-seo-suite::generic.health_seo_services_passed')
                : __('capell-seo-suite::generic.health_seo_services_failed', ['services' => implode(', ', $unresolvableServices)]),
            remediation: $unresolvableServices === []
                ? null
                : (string) __('capell-seo-suite::generic.health_seo_services_remediation'),
        );
    }

    /**
     * Asserts the structured-data registry contains the built-in templates required by schema graph output.
     */
    public function schemaTemplateRegistryCheck(): DoctorCheckResultData
    {
        $missingTemplates = $this->missingSchemaTemplates();

        return new DoctorCheckResultData(
            label: __('capell-seo-suite::generic.health_schema_templates_label'),
            passed: $missingTemplates === [],
            message: $missingTemplates === []
                ? __('capell-seo-suite::generic.health_schema_templates_passed')
                : __('capell-seo-suite::generic.health_schema_templates_failed', ['templates' => implode(', ', $missingTemplates)]),
            remediation: $missingTemplates === []
                ? null
                : (string) __('capell-seo-suite::generic.health_schema_templates_remediation'),
        );
    }

    /**
     * Asserts AI crawler defaults are configured so robots.txt and AI Discovery policy can be generated.
     */
    public function aiCrawlerPolicyCheck(): DoctorCheckResultData
    {
        $missingCrawlerAgents = $this->missingAiCrawlerAgents();

        return new DoctorCheckResultData(
            label: __('capell-seo-suite::generic.health_ai_crawler_policy_label'),
            passed: $missingCrawlerAgents === [],
            message: $missingCrawlerAgents === []
                ? __('capell-seo-suite::generic.health_ai_crawler_policy_passed')
                : __('capell-seo-suite::generic.health_ai_crawler_policy_failed', ['agents' => implode(', ', $missingCrawlerAgents)]),
            remediation: $missingCrawlerAgents === []
                ? null
                : (string) __('capell-seo-suite::generic.health_ai_crawler_policy_remediation'),
        );
    }

    /**
     * @return list<string>
     */
    public function missingTables(): array
    {
        return array_values(collect(self::REQUIRED_TABLES)
            ->reject(static fn (string $tableName): bool => Schema::hasTable($tableName))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function missingAiDiscoveryRoutes(): array
    {
        return array_values(collect(self::REQUIRED_AI_DISCOVERY_ROUTES)
            ->reject(static fn (string $routeName): bool => Route::has($routeName))
            ->values()
            ->all());
    }

    /**
     * @return list<class-string>
     */
    public function unresolvableSeoServices(): array
    {
        $unresolvableServices = [];

        foreach (self::REQUIRED_SEO_SERVICES as $serviceClass) {
            try {
                if (! resolve($serviceClass) instanceof $serviceClass) {
                    $unresolvableServices[] = $serviceClass;
                }
            } catch (Throwable) {
                $unresolvableServices[] = $serviceClass;
            }
        }

        return $unresolvableServices;
    }

    /**
     * @return list<string>
     */
    public function missingSchemaTemplates(): array
    {
        try {
            /** @var SchemaTemplateRegistry $registry */
            $registry = resolve(SchemaTemplateRegistry::class);
        } catch (Throwable) {
            return array_map(
                static fn (SchemaTemplateTypeEnum $type): string => $type->value,
                self::REQUIRED_SCHEMA_TEMPLATES,
            );
        }

        return array_values(collect(self::REQUIRED_SCHEMA_TEMPLATES)
            ->reject(static fn (SchemaTemplateTypeEnum $type): bool => $registry->get($type) instanceof SchemaTemplate)
            ->map(static fn (SchemaTemplateTypeEnum $type): string => $type->value)
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function missingAiCrawlerAgents(): array
    {
        $configuredRules = config('capell-seo-suite.ai_discovery.default_crawler_rules', []);
        $configuredAgents = [];

        if (is_array($configuredRules)) {
            foreach ($configuredRules as $rule) {
                if (! is_array($rule)) {
                    continue;
                }

                if (! is_string($rule['user_agent'] ?? null)) {
                    continue;
                }

                if (trim($rule['user_agent']) === '') {
                    continue;
                }

                $configuredAgents[] = $rule['user_agent'];
            }
        }

        return array_values(collect(self::REQUIRED_AI_CRAWLER_AGENTS)
            ->reject(static fn (string $userAgent): bool => in_array($userAgent, $configuredAgents, true))
            ->values()
            ->all());
    }
}

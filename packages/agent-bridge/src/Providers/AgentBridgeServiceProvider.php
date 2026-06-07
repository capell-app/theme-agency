<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Providers;

use Capell\Admin\Contracts\Extenders\UserSchemaExtender;
use Capell\Admin\Data\Extensions\ExtensionManagementSurfaceData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Support\Bridges\AdminBridgeRegistrar;
use Capell\AgentBridge\Actions\Cache\ClearCapellCacheCapabilityAction;
use Capell\AgentBridge\Actions\Pages\CreateDraftPageCapabilityAction;
use Capell\AgentBridge\Actions\Pages\DisablePageCapabilityAction;
use Capell\AgentBridge\Actions\Pages\InspectPagePublishingReadinessCapabilityAction;
use Capell\AgentBridge\Actions\Pages\UpdateDraftPageCapabilityAction;
use Capell\AgentBridge\Bridges\AgentBridgeAdminBridge;
use Capell\AgentBridge\Console\Commands\PruneAgentBridgeAuditEntriesCommand;
use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityProvider;
use Capell\AgentBridge\Data\Capabilities\ClearCacheCapabilityInputData;
use Capell\AgentBridge\Data\Capabilities\CreateDraftPageCapabilityInputData;
use Capell\AgentBridge\Data\Capabilities\PageIdCapabilityInputData;
use Capell\AgentBridge\Data\Capabilities\UpdateDraftPageCapabilityInputData;
use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Enums\CapabilityServerEnum;
use Capell\AgentBridge\Extenders\AgentBridgeUserSchemaExtender;
use Capell\AgentBridge\Filament\Pages\CapellAgentBridgePromptBuilderPage;
use Capell\AgentBridge\Filament\Settings\AgentBridgeSettingsSchema;
use Capell\AgentBridge\Livewire\PromptBuilderToolbarAction;
use Capell\AgentBridge\Settings\AgentBridgeSettings;
use Capell\AgentBridge\Support\CapabilitySchemas;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\AgentBridge\Tools\Boost\ListBoostCapabilitiesTool;
use Capell\AgentBridge\Tools\Boost\PreviewBoostCapabilityTool;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Settings\SettingsGroupMetadata;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Laravel\Boost\AgentBridge\Boost;
use Livewire\Livewire;
use Override;
use Throwable;

final class AgentBridgeServiceProvider extends ServiceProvider
{
    public static string $packageName = 'capell-app/agent-bridge';

    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/capell-agent-bridge.php', 'capell-agent-bridge');
        $this->registerSettingsIntegration();
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'capell-agent-bridge');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'capell-agent-bridge');
        Livewire::component('capell-agent-bridge.prompt-builder-toolbar-action', PromptBuilderToolbarAction::class);

        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->app->singleton(CapellAgentBridgeCapabilityRegistry::class, fn (): CapellAgentBridgeCapabilityRegistry => new CapellAgentBridgeCapabilityRegistry);

        $this->publishes([
            __DIR__ . '/../../config/capell-agent-bridge.php' => config_path('capell-agent-bridge.php'),
        ], 'capell-agent-bridge-config');

        $this->loadRoutesFrom(__DIR__ . '/../../routes/agent-bridge.php');

        $this->registerSettingsIntegration();
        $this->registerAdminIntegration();
        $this->registerBoostIntegration();
        $this->registerBuiltInCapabilities();
        $this->registerTaggedCapabilityProviders();
        $this->registerCommands();
    }

    private function isPackageInstalled(): bool
    {
        if (! class_exists(CapellCore::class)) {
            return true;
        }

        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerBoostIntegration(): void
    {
        if (! class_exists(Boost::class)) {
            return;
        }

        $includedTools = config('boost.agent-bridge.tools.include', []);

        config([
            'boost.agent-bridge.tools.include' => array_values(array_unique([
                ...$includedTools,
                ListBoostCapabilitiesTool::class,
                PreviewBoostCapabilityTool::class,
            ])),
        ]);
    }

    private function registerAdminIntegration(): void
    {
        $adminFacade = CapellAdmin::class;
        $filamentPage = Page::class;
        $promptBuilderPage = CapellAgentBridgePromptBuilderPage::class;

        if (! class_exists($adminFacade) || ! class_exists($filamentPage)) {
            return;
        }

        if (! class_exists($promptBuilderPage)) {
            return;
        }

        if ($this->supportsAdminBridges()) {
            CapellAdmin::registerAdminBridge(self::$packageName, AgentBridgeAdminBridge::class);
            CapellAdmin::bootAdminBridges(self::$packageName);
        } else {
            $adminFacade::registerExtensionPage(self::$packageName, $promptBuilderPage);
            $this->registerUserResourceBridgeFallback();
        }

        if (! class_exists(FilamentView::class) || ! class_exists(PanelsRenderHook::class)) {
            return;
        }

        FilamentView::registerRenderHook(
            PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
            fn (): string => Blade::render('@livewire($component)', ['component' => 'capell-agent-bridge.prompt-builder-toolbar-action']),
        );
    }

    private function registerSettingsIntegration(): void
    {
        $settings = config('settings.settings', []);

        if (! in_array(AgentBridgeSettings::class, $settings, true)) {
            $settings[] = AgentBridgeSettings::class;
        }

        config(['settings.settings' => $settings]);

        if (! class_exists(SettingsSchemaRegistry::class)) {
            return;
        }

        if (! $this->app->bound(SettingsSchemaRegistry::class)) {
            $this->app->afterResolving(
                SettingsSchemaRegistry::class,
                fn (SettingsSchemaRegistry $registry): SettingsSchemaRegistry => $this->registerSettingsSchemas($registry),
            );

            return;
        }

        /** @var SettingsSchemaRegistry $registry */
        $registry = $this->app->make(SettingsSchemaRegistry::class);

        $this->registerSettingsSchemas($registry);
    }

    private function registerSettingsSchemas(SettingsSchemaRegistry $registry): SettingsSchemaRegistry
    {
        $registry->registerSettingsClass(AgentBridgeSettings::group(), AgentBridgeSettings::class);
        $registry->register(AgentBridgeSettings::group(), AgentBridgeSettingsSchema::class);
        CapellAdmin::registerExtensionManagementSurface(ExtensionManagementSurfaceData::settings(
            packageName: self::$packageName,
            label: 'capell-agent-bridge::admin.settings_title',
            settingsGroup: AgentBridgeSettings::group(),
            icon: Heroicon::OutlinedSparkles,
        ));

        if (class_exists(SettingsGroupMetadata::class)) {
            $registry->registerMetadata(new SettingsGroupMetadata(
                group: AgentBridgeSettings::group(),
                label: 'capell-agent-bridge::admin.settings_title',
                icon: Heroicon::OutlinedSparkles,
                navigationGroup: 'capell-admin::navigation.group_system',
                navigationSort: 94,
                packageName: self::$packageName,
            ));
        }

        return $registry;
    }

    private function registerUserResourceBridgeFallback(): void
    {
        if (! interface_exists(UserSchemaExtender::class)) {
            return;
        }

        $this->app->bind(AgentBridgeUserSchemaExtender::class);
        $this->app->tag([AgentBridgeUserSchemaExtender::class], UserSchemaExtender::TAG);
    }

    private function supportsAdminBridges(): bool
    {
        try {
            $admin = CapellAdmin::getFacadeRoot();
        } catch (Throwable) {
            return false;
        }

        return is_object($admin)
            && method_exists($admin, 'registerAdminBridge')
            && method_exists($admin, 'bootAdminBridges')
            && class_exists(AgentBridgeAdminBridge::class)
            && class_exists(AdminBridgeRegistrar::class);

    }

    private function registerBuiltInCapabilities(): void
    {
        $registry = $this->app->make(CapellAgentBridgeCapabilityRegistry::class);

        $registry->register(new CapabilityData(
            key: 'capell.cache.clear',
            name: $this->translation('capell-agent-bridge::admin.capability_cache_clear_name'),
            description: $this->translation('capell-agent-bridge::admin.capability_cache_clear_description'),
            scope: 'capell.cache.run',
            server: CapabilityServerEnum::Site,
            risk: CapabilityRiskEnum::Medium,
            actionClass: ClearCapellCacheCapabilityAction::class,
            requiredPackage: 'capell-app/core',
            inputDataClass: ClearCacheCapabilityInputData::class,
            outputDataClass: CapabilityResultData::class,
            inputSchema: CapabilitySchemas::emptyObject(),
            outputSchema: CapabilitySchemas::capabilityResultOutput(),
            auditEvent: 'capell_agent-bridge.cache.clear',
        ));

        $registry->register(new CapabilityData(
            key: 'capell.pages.create_draft',
            name: $this->translation('capell-agent-bridge::admin.capability_pages_create_draft_name'),
            description: $this->translation('capell-agent-bridge::admin.capability_pages_create_draft_description'),
            scope: 'capell.pages.write',
            server: CapabilityServerEnum::Site,
            risk: CapabilityRiskEnum::High,
            actionClass: CreateDraftPageCapabilityAction::class,
            requiredPackage: 'capell-app/core',
            inputDataClass: CreateDraftPageCapabilityInputData::class,
            outputDataClass: CapabilityResultData::class,
            inputSchema: CapabilitySchemas::createDraftPageInput(),
            outputSchema: CapabilitySchemas::capabilityResultOutput(),
            auditEvent: 'capell_agent-bridge.pages.create_draft',
        ));

        $registry->register(new CapabilityData(
            key: 'capell.pages.update_draft',
            name: $this->translation('capell-agent-bridge::admin.capability_pages_update_draft_name'),
            description: $this->translation('capell-agent-bridge::admin.capability_pages_update_draft_description'),
            scope: 'capell.pages.write',
            server: CapabilityServerEnum::Site,
            risk: CapabilityRiskEnum::High,
            actionClass: UpdateDraftPageCapabilityAction::class,
            requiredPackage: 'capell-app/core',
            inputDataClass: UpdateDraftPageCapabilityInputData::class,
            outputDataClass: CapabilityResultData::class,
            inputSchema: CapabilitySchemas::updateDraftPageInput(),
            outputSchema: CapabilitySchemas::capabilityResultOutput(),
            auditEvent: 'capell_agent-bridge.pages.update_draft',
        ));

        $registry->register(new CapabilityData(
            key: 'capell.pages.disable',
            name: $this->translation('capell-agent-bridge::admin.capability_pages_disable_name'),
            description: $this->translation('capell-agent-bridge::admin.capability_pages_disable_description'),
            scope: 'capell.pages.write',
            server: CapabilityServerEnum::Site,
            risk: CapabilityRiskEnum::High,
            actionClass: DisablePageCapabilityAction::class,
            requiredPackage: 'capell-app/core',
            inputDataClass: PageIdCapabilityInputData::class,
            outputDataClass: CapabilityResultData::class,
            inputSchema: CapabilitySchemas::pageIdInput(),
            outputSchema: CapabilitySchemas::capabilityResultOutput(),
            auditEvent: 'capell_agent-bridge.pages.disable',
        ));

        $registry->register(new CapabilityData(
            key: 'capell.pages.inspect_readiness',
            name: $this->translation('capell-agent-bridge::admin.capability_pages_inspect_readiness_name'),
            description: $this->translation('capell-agent-bridge::admin.capability_pages_inspect_readiness_description'),
            scope: 'capell.pages.read',
            server: CapabilityServerEnum::Site,
            risk: CapabilityRiskEnum::Read,
            actionClass: InspectPagePublishingReadinessCapabilityAction::class,
            requiredPackage: 'capell-app/core',
            inputDataClass: PageIdCapabilityInputData::class,
            outputDataClass: CapabilityResultData::class,
            inputSchema: CapabilitySchemas::pageIdInput(),
            outputSchema: CapabilitySchemas::capabilityResultOutput(),
            requiresConfirmation: false,
            auditEvent: 'capell_agent-bridge.pages.inspect_readiness',
        ));
    }

    private function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            PruneAgentBridgeAuditEntriesCommand::class,
        ]);
    }

    private function translation(string $key): string
    {
        $value = __($key);

        return is_string($value) ? $value : $key;
    }

    private function registerTaggedCapabilityProviders(): void
    {
        $registry = $this->app->make(CapellAgentBridgeCapabilityRegistry::class);

        foreach ($this->app->tagged(CapellAgentBridgeCapabilityProvider::class) as $provider) {
            if ($provider instanceof CapellAgentBridgeCapabilityProvider) {
                $provider->registerCapabilities($registry);
            }
        }
    }
}

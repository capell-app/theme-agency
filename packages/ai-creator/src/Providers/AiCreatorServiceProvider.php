<?php

declare(strict_types=1);

namespace Capell\AiCreator\Providers;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityProvider;
use Capell\AiCreator\AgentBridge\AiCreatorAgentBridgeCapabilityProvider;
use Capell\AiCreator\Listeners\BuildSiteFromInstalledSpecListener;
use Capell\AiCreator\Models\AiCreatorSession;
use Capell\Core\Events\CapellInstalled;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Illuminate\Support\Facades\Event;
use Override;
use Spatie\LaravelPackageTools\Package;

final class AiCreatorServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-ai-creator';

    public static string $packageName = 'capell-app/ai-creator';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasTranslations()
            ->hasMigrations([
                '2026_06_22_000001_create_capell_ai_creator_sessions_table',
            ]);
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(AiCreatorAgentBridgeCapabilityProvider::class);
        $this->app->tag(AiCreatorAgentBridgeCapabilityProvider::class, CapellAgentBridgeCapabilityProvider::class);
    }

    public function packageBooted(): void
    {
        // Registered unconditionally: a fresh `capell:install --spec` dispatches
        // CapellInstalled during the very install that marks the package
        // installed, so guarding this behind isPackageInstalled() would miss it.
        Event::listen(CapellInstalled::class, BuildSiteFromInstalledSpecListener::class);

        if (! $this->isPackageInstalled()) {
            return;
        }

        CapellCore::registerModels([
            AiCreatorSession::class,
        ]);
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }
}

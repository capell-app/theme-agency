<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Providers;

use Capell\AgentDelivery\Contracts\AgentDeliveryContributor;
use Capell\AgentDelivery\Support\AgentDeliveryRegistry;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\LaravelPackageTools\Package;

final class AgentDeliveryServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-agent-delivery';

    public static string $packageName = 'capell-app/agent-delivery';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile();

        if (file_exists(__DIR__ . '/../../routes/agent-delivery.php')) {
            $package->hasRoute('agent-delivery');
        }
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(AgentDeliveryRegistry::class);
    }

    public function packageBooted(): void
    {
        RateLimiter::for('capell-agent-delivery', function (Request $request): Limit {
            $maxAttempts = config('capell-agent-delivery.public_pages.rate_limit_per_minute', 60);
            $attempts = is_int($maxAttempts) ? $maxAttempts : 60;

            return Limit::perMinute($attempts)
                ->by((string) $request->ip());
        });

        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->registerTaggedContributors();
    }

    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerTaggedContributors(): void
    {
        /** @var AgentDeliveryRegistry $registry */
        $registry = $this->app->make(AgentDeliveryRegistry::class);

        foreach ($this->app->tagged(AgentDeliveryContributor::TAG) as $contributor) {
            if ($contributor instanceof AgentDeliveryContributor) {
                $registry->register($contributor);
            }
        }
    }
}

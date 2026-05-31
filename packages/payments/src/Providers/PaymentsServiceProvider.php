<?php

declare(strict_types=1);

namespace Capell\Payments\Providers;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Payments\Contracts\PaymentGateway;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentCustomer;
use Capell\Payments\Models\PaymentIntent;
use Capell\Payments\Models\Subscription;
use Capell\Payments\Support\Gateways\StripePaymentGateway;
use Illuminate\Contracts\Foundation\Application;
use Spatie\LaravelPackageTools\Package;

final class PaymentsServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-payments';

    public static string $packageName = 'capell-app/payments';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                '2026_05_31_000001_create_payment_customers_table',
                '2026_05_31_000002_create_payment_checkout_sessions_table',
                '2026_05_31_000003_create_payment_intents_table',
                '2026_05_31_000004_create_payment_subscriptions_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PaymentGateway::class, static fn (Application $app): PaymentGateway => new StripePaymentGateway);

        $this->app->booted(function (): void {
            if (! CapellCore::isPackageInstalled(self::$packageName)) {
                return;
            }

            $this
                ->registerModels()
                ->registerProtectedTables();
        });
    }

    private function registerModels(): self
    {
        CapellCore::registerModels([
            PaymentCustomer::class,
            CheckoutSession::class,
            PaymentIntent::class,
            Subscription::class,
        ]);

        return $this;
    }

    private function registerProtectedTables(): self
    {
        CapellCore::registerProtectedTable('payment_customers');
        CapellCore::registerProtectedTable('payment_checkout_sessions');
        CapellCore::registerProtectedTable('payment_intents');
        CapellCore::registerProtectedTable('payment_subscriptions');

        return $this;
    }
}

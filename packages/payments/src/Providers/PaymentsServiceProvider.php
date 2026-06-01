<?php

declare(strict_types=1);

namespace Capell\Payments\Providers;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Core\Support\Settings\SettingsGroupMetadata;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Capell\Payments\Contracts\PaymentGateway;
use Capell\Payments\Filament\Settings\PaymentsSettingsSchema;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentCustomer;
use Capell\Payments\Models\PaymentDispute;
use Capell\Payments\Models\PaymentDownloadEntitlement;
use Capell\Payments\Models\PaymentIntent;
use Capell\Payments\Models\PaymentRefund;
use Capell\Payments\Models\PaymentWebhookEvent;
use Capell\Payments\Models\Subscription;
use Capell\Payments\Settings\PaymentsSettings;
use Capell\Payments\Support\CustomerPortal\PaymentsPortalDashboardItemProvider;
use Capell\Payments\Support\Fulfillment\PaidDownloadFulfillmentHandler;
use Capell\Payments\Support\Gateways\StripePaymentGateway;
use Filament\Support\Icons\Heroicon;
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
                '2026_05_31_000005_create_payment_webhook_events_table',
                '2026_05_31_000006_create_payment_refunds_table',
                '2026_05_31_000007_create_payment_disputes_table',
                '2026_05_31_000009_create_payment_download_entitlements_table',
            ])
            ->hasRoute('web');
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
                ->registerSettings()
                ->registerProtectedTables()
                ->registerPaymentFulfillmentHandlers()
                ->registerCustomerPortalDashboardItems();
        });
    }

    private function registerModels(): self
    {
        CapellCore::registerModels([
            PaymentCustomer::class,
            CheckoutSession::class,
            PaymentIntent::class,
            Subscription::class,
            PaymentWebhookEvent::class,
            PaymentRefund::class,
            PaymentDispute::class,
            PaymentDownloadEntitlement::class,
        ]);

        return $this;
    }

    private function registerProtectedTables(): self
    {
        CapellCore::registerProtectedTable('payment_customers');
        CapellCore::registerProtectedTable('payment_checkout_sessions');
        CapellCore::registerProtectedTable('payment_intents');
        CapellCore::registerProtectedTable('payment_subscriptions');
        CapellCore::registerProtectedTable('payment_webhook_events');
        CapellCore::registerProtectedTable('payment_refunds');
        CapellCore::registerProtectedTable('payment_disputes');
        CapellCore::registerProtectedTable('payment_download_entitlements');

        return $this;
    }

    private function registerPaymentFulfillmentHandlers(): self
    {
        $this->app->singleton(PaidDownloadFulfillmentHandler::class);
        $this->app->tag([PaidDownloadFulfillmentHandler::class], 'capell.payments.fulfillment_handler');

        return $this;
    }

    private function registerSettings(): self
    {
        if (! class_exists(SettingsSchemaRegistry::class) || ! class_exists(PaymentsSettings::class) || ! class_exists(PaymentsSettingsSchema::class)) {
            return $this;
        }

        /** @var SettingsSchemaRegistry $registry */
        $registry = $this->app->make(SettingsSchemaRegistry::class);

        $registry->registerSettingsClass(PaymentsSettings::group(), PaymentsSettings::class);
        $registry->registerMetadata(new SettingsGroupMetadata(
            group: PaymentsSettings::group(),
            label: 'capell-payments::settings.title',
            icon: Heroicon::OutlinedCreditCard,
            navigationGroup: 'capell-admin::navigation.group_system',
            navigationSort: 96,
            packageName: self::$packageName,
        ));
        $registry->register(PaymentsSettings::group(), PaymentsSettingsSchema::class);

        return $this;
    }

    private function registerCustomerPortalDashboardItems(): self
    {
        if (! class_exists('Capell\\CustomerPortal\\Support\\PortalDashboardItemRegistry')
            || ! interface_exists('Capell\\CustomerPortal\\Contracts\\PortalDashboardItemProvider')) {
            return $this;
        }

        /** @var object $registry */
        $registry = $this->app->make('Capell\\CustomerPortal\\Support\\PortalDashboardItemRegistry');

        if (! method_exists($registry, 'register')) {
            return $this;
        }

        $registry->register('payments.billing', PaymentsPortalDashboardItemProvider::class);

        return $this;
    }
}

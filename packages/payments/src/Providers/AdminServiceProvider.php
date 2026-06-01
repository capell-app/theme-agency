<?php

declare(strict_types=1);

namespace Capell\Payments\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Support\CapellAdminManager;
use Capell\Core\Facades\CapellCore;
use Capell\Payments\Enums\ResourceEnum;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentCustomer;
use Capell\Payments\Models\PaymentDispute;
use Capell\Payments\Models\PaymentIntent;
use Capell\Payments\Models\PaymentRefund;
use Capell\Payments\Models\PaymentWebhookEvent;
use Capell\Payments\Models\Subscription;
use Capell\Payments\Policies\CheckoutSessionPolicy;
use Capell\Payments\Policies\PaymentCustomerPolicy;
use Capell\Payments\Policies\PaymentDisputePolicy;
use Capell\Payments\Policies\PaymentIntentPolicy;
use Capell\Payments\Policies\PaymentRefundPolicy;
use Capell\Payments\Policies\PaymentWebhookEventPolicy;
use Capell\Payments\Policies\SubscriptionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->booted(function (): void {
            if (! CapellCore::isPackageInstalled(PaymentsServiceProvider::$packageName) || ! $this->app->bound(CapellAdminManager::class)) {
                return;
            }

            $this
                ->registerPolicies()
                ->registerResources();
        });
    }

    private function registerPolicies(): self
    {
        Gate::policy(PaymentCustomer::class, PaymentCustomerPolicy::class);
        Gate::policy(CheckoutSession::class, CheckoutSessionPolicy::class);
        Gate::policy(PaymentIntent::class, PaymentIntentPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(PaymentWebhookEvent::class, PaymentWebhookEventPolicy::class);
        Gate::policy(PaymentRefund::class, PaymentRefundPolicy::class);
        Gate::policy(PaymentDispute::class, PaymentDisputePolicy::class);

        return $this;
    }

    private function registerResources(): self
    {
        foreach (ResourceEnum::cases() as $resource) {
            CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
                class: $resource->value,
                group: $resource->name,
            ));
        }

        return $this;
    }
}

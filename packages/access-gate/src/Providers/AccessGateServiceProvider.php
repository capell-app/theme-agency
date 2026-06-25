<?php

declare(strict_types=1);

namespace Capell\AccessGate\Providers;

use Capell\AccessGate\Actions\SubmitAccessGatePublicAction;
use Capell\AccessGate\Console\Commands\AccessGateAuditExportCommand;
use Capell\AccessGate\Console\Commands\AccessGateDoctorCommand;
use Capell\AccessGate\Console\Commands\AccessGateInstallCommand;
use Capell\AccessGate\Console\Commands\AccessGatePruneCommand;
use Capell\AccessGate\Console\Commands\AccessGateSetupCommand;
use Capell\AccessGate\Contracts\AccessRequestMethod;
use Capell\AccessGate\Contracts\RegistrationField;
use Capell\AccessGate\Enums\ResourceEnum;
use Capell\AccessGate\Filament\Widgets\PendingAccessRequestsFilamentWidget;
use Capell\AccessGate\Frontend\Rules\AccessGateAreaStatusCondition;
use Capell\AccessGate\Frontend\Rules\AccessGateRegistrationStatusCondition;
use Capell\AccessGate\Frontend\Rules\HasActiveAccessGateGrantCondition;
use Capell\AccessGate\Frontend\Rules\MissingActiveAccessGateGrantCondition;
use Capell\AccessGate\Http\Middleware\AccessGateMiddleware;
use Capell\AccessGate\Listeners\NotifyAdminsOfAccessRequest;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\BrowserToken;
use Capell\AccessGate\Models\ClaimToken;
use Capell\AccessGate\Models\Event;
use Capell\AccessGate\Models\Grant;
use Capell\AccessGate\Models\Registration;
use Capell\AccessGate\Policies\AccessAreaPolicy;
use Capell\AccessGate\Policies\AccessGateEventPolicy;
use Capell\AccessGate\Policies\BrowserTokenPolicy;
use Capell\AccessGate\Policies\ClaimTokenPolicy;
use Capell\AccessGate\Policies\GrantPolicy;
use Capell\AccessGate\Policies\RegistrationPolicy;
use Capell\AccessGate\Support\AccessGateDiagnosticsService;
use Capell\AccessGate\Support\AccessRequestMethodRegistry;
use Capell\AccessGate\Support\CustomerPortal\AccessGatePortalSelfServiceItemProvider;
use Capell\AccessGate\Support\Payments\AccessGatePaymentFulfillmentHandler;
use Capell\AccessGate\Support\RegistrationFieldRegistry;
use Capell\AccessGate\Support\RenderHooks\RegisterAnnouncementBarHook;
use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\CustomerPortal\Contracts\PortalSelfServiceItemProvider;
use Capell\CustomerPortal\Support\PortalSelfServiceItemRegistry;
use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Support\Render\FrontendHookRegistrar;
use Capell\Frontend\Support\Rules\FrontendRuleConditionRegistry;
use Capell\Payments\Contracts\PaymentFulfillmentHandler;
use Capell\PublicActions\Support\PublicActionHandlerRegistry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Override;
use Spatie\LaravelPackageTools\Package;

final class AccessGateServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-access-gate';

    public static string $packageName = 'capell-app/access-gate';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile('access-gate')
            ->hasTranslations()
            ->hasViews(self::$name)
            ->hasRoute('web')
            ->hasCommands([
                AccessGateAuditExportCommand::class,
                AccessGateDoctorCommand::class,
                AccessGateInstallCommand::class,
                AccessGatePruneCommand::class,
                AccessGateSetupCommand::class,
            ])
            ->hasMigrations([
                '2026_05_10_190838_01_create_access_gate_areas_table',
                '2026_05_10_190838_02_create_access_gate_registrations_table',
                '2026_05_10_190838_03_create_access_gate_grants_table',
                '2026_05_10_190838_04_create_access_gate_claim_tokens_table',
                '2026_05_10_190838_05_create_access_gate_browser_tokens_table',
                '2026_05_10_190838_06_create_access_gate_events_table',
                '2026_06_07_000001_add_claim_landing_url_to_access_gate_areas_table',
                '2026_06_14_000001_add_announcement_bar_fields_to_access_gate_areas_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(RegistrationFieldRegistry::class);
        $this->app->singleton(AccessRequestMethodRegistry::class);
        $this->registerMiddlewareAliases();
        $this->registerMiddlewarePriority();
        $this->app->booted(function (): void {
            $this->applyMiddlewarePriority($this->app->make(Router::class));
            $this->registerRateLimiters();
            $this->registerConfiguredRegistrationFields();
            $this->registerConfiguredAccessRequestMethods();
            $this->registerPublicActionHandler();

            if (! $this->hasCapellCore()) {
                return;
            }

            if (! $this->isPackageInstalled()) {
                return;
            }

            $this
                ->registerModels()
                ->registerPolicies()
                ->registerAdminResources()
                ->registerFrontendRuleConditions()
                ->registerFrontendRenderHooks()
                ->registerProtectedTables()
                ->registerCustomerPortalIntegrations()
                ->registerPaymentFulfillmentHandler();
        });
    }

    public function packageBooted(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', self::$name);

        if (! $this->hasCapellCore()) {
            return;
        }

        if (! $this->isPackageInstalled()) {
            return;
        }

        Relation::morphMap([
            'area' => Area::class,
            'registration' => Registration::class,
            'grant' => Grant::class,
            'claim_token' => ClaimToken::class,
            'browser_token' => BrowserToken::class,
            'event' => Event::class,
        ], merge: true);

        $this->registerAccessRequestNotifications();
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerAccessRequestNotifications(): self
    {
        Registration::created(function (Registration $registration): void {
            $this->app->make(NotifyAdminsOfAccessRequest::class)->handle($registration);
        });

        return $this;
    }

    private function registerFrontendRuleConditions(): self
    {
        if (! class_exists(FrontendRuleConditionRegistry::class)) {
            return $this;
        }

        $this->app->afterResolving(FrontendRuleConditionRegistry::class, function (FrontendRuleConditionRegistry $registry): void {
            $registry->register(AccessGateAreaStatusCondition::class);
            $registry->register(AccessGateRegistrationStatusCondition::class);
            $registry->register(HasActiveAccessGateGrantCondition::class);
            $registry->register(MissingActiveAccessGateGrantCondition::class);
        });

        return $this;
    }

    private function registerFrontendRenderHooks(): self
    {
        if (! class_exists(FrontendHookRegistrar::class) || ! $this->app->bound(FrontendHookRegistrar::class)) {
            return $this;
        }

        $this->app->make(FrontendHookRegistrar::class)->contribute(
            location: RenderHookLocation::BodyStart,
            extension: new RegisterAnnouncementBarHook,
            owner: self::$packageName,
            key: 'announcement-bar',
            cacheSafe: true,
        );

        return $this;
    }

    private function registerConfiguredAccessRequestMethods(): self
    {
        $methods = config('access-gate.registration.identity_methods', []);

        if (! is_array($methods)) {
            return $this;
        }

        $registry = $this->app->make(AccessRequestMethodRegistry::class);

        foreach ($methods as $method) {
            if (! is_string($method)) {
                continue;
            }

            if (! is_a($method, AccessRequestMethod::class, true)) {
                continue;
            }

            $registry->register($method);
        }

        return $this;
    }

    private function registerConfiguredRegistrationFields(): self
    {
        $fields = config('access-gate.registration.fields', []);

        if (! is_array($fields)) {
            return $this;
        }

        $registry = $this->app->make(RegistrationFieldRegistry::class);

        foreach ($fields as $field) {
            if (! is_string($field)) {
                continue;
            }

            if (! is_a($field, RegistrationField::class, true)) {
                continue;
            }

            $registry->register($field);
        }

        return $this;
    }

    private function registerPublicActionHandler(): self
    {
        if (! class_exists(PublicActionHandlerRegistry::class)) {
            return $this;
        }

        $registerHandler = function (PublicActionHandlerRegistry $registry): void {
            $registry->register('access-gate.request', SubmitAccessGatePublicAction::class);
        };

        if ($this->app->bound(PublicActionHandlerRegistry::class)) {
            $registerHandler($this->app->make(PublicActionHandlerRegistry::class));
        }

        $this->app->afterResolving(PublicActionHandlerRegistry::class, $registerHandler);

        return $this;
    }

    private function registerMiddlewareAliases(): self
    {
        Route::aliasMiddleware('access-gate', AccessGateMiddleware::class);

        return $this;
    }

    private function registerRateLimiters(): self
    {
        RateLimiter::for('access-gate-request', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email', ''));
            $area = (string) $request->route('area', '');
            $key = hash('sha256', $area . '|' . $email . '|' . $request->ip());

            return Limit::perMinute(6)->by($key);
        });

        RateLimiter::for('access-gate-logout', function (Request $request): Limit {
            $area = (string) $request->route('area', '');
            $key = hash('sha256', $area . '|' . $request->ip());

            return Limit::perMinute(20)->by($key);
        });

        return $this;
    }

    private function registerMiddlewarePriority(): self
    {
        $this->applyMiddlewarePriority($this->app->make(Router::class));

        $this->app->afterResolving(Router::class, function (Router $router): void {
            $this->applyMiddlewarePriority($router);
        });

        return $this;
    }

    private function applyMiddlewarePriority(Router $router): void
    {
        $diagnostics = $this->app->make(AccessGateDiagnosticsService::class);

        $priority = [
            EncryptCookies::class,
            AccessGateMiddleware::class,
            'access-gate',
            ...$diagnostics->pageCacheMiddlewarePriorityNames($router),
        ];

        $orderedPriority = collect($priority)
            ->merge($this->existingMiddlewarePriority($router))
            ->unique()
            ->values()
            ->all();

        $router->middlewarePriority = $orderedPriority;

        if (! $this->app->bound(HttpKernel::class)) {
            return;
        }

        $kernel = $this->app->make(HttpKernel::class);

        if (method_exists($kernel, 'setMiddlewarePriority')) {
            $kernel->setMiddlewarePriority($orderedPriority);
        }
    }

    private function hasCapellCore(): bool
    {
        return class_exists(CapellCore::class);
    }

    private function registerModels(): self
    {
        $models = [
            Area::class,
            Registration::class,
            Grant::class,
            ClaimToken::class,
            BrowserToken::class,
            Event::class,
        ];

        $this->surface()->models($models);
        CapellCore::registerModels($models);

        return $this;
    }

    private function registerPolicies(): self
    {
        Gate::policy(Area::class, AccessAreaPolicy::class);
        Gate::policy(Registration::class, RegistrationPolicy::class);
        Gate::policy(Grant::class, GrantPolicy::class);
        Gate::policy(ClaimToken::class, ClaimTokenPolicy::class);
        Gate::policy(BrowserToken::class, BrowserTokenPolicy::class);
        Gate::policy(Event::class, AccessGateEventPolicy::class);

        return $this;
    }

    private function registerAdminResources(): self
    {
        if (! class_exists(CapellAdmin::class) || ! class_exists(AdminSurfaceContributionData::class)) {
            return $this;
        }

        foreach (ResourceEnum::cases() as $resource) {
            CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
                class: $resource->value,
                group: $resource->name,
            ));
        }

        CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::widget(
            PendingAccessRequestsFilamentWidget::class,
        ));

        return $this;
    }

    private function registerProtectedTables(): self
    {
        foreach ($this->protectedTables() as $tableName) {
            CapellCore::registerProtectedTable(fn (): string => $tableName);
        }

        return $this;
    }

    private function registerPaymentFulfillmentHandler(): self
    {
        if (! interface_exists(PaymentFulfillmentHandler::class)) {
            return $this;
        }

        $this->app->singleton(AccessGatePaymentFulfillmentHandler::class);
        $this->app->tag([AccessGatePaymentFulfillmentHandler::class], PaymentFulfillmentHandler::TAG);

        return $this;
    }

    private function registerCustomerPortalIntegrations(): self
    {
        if (! class_exists(PortalSelfServiceItemRegistry::class)
            || ! interface_exists(PortalSelfServiceItemProvider::class)) {
            return $this;
        }

        /** @var object $registry */
        $registry = $this->app->make(PortalSelfServiceItemRegistry::class);

        if (! method_exists($registry, 'register')) {
            return $this;
        }

        $registry->register('access-gate.gated-resources', AccessGatePortalSelfServiceItemProvider::class);

        return $this;
    }

    /**
     * @return list<string>
     */
    private function protectedTables(): array
    {
        return [
            'access_gate_areas',
            'access_gate_registrations',
            'access_gate_grants',
            'access_gate_claim_tokens',
            'access_gate_browser_tokens',
            'access_gate_events',
        ];
    }

    /**
     * @return list<string>
     */
    private function existingMiddlewarePriority(Router $router): array
    {
        if (! $this->app->bound(HttpKernel::class)) {
            return $this->middlewarePriorityList($router->middlewarePriority);
        }

        $kernel = $this->app->make(HttpKernel::class);

        if (! method_exists($kernel, 'getMiddlewarePriority')) {
            return $this->middlewarePriorityList($router->middlewarePriority);
        }

        return $this->middlewarePriorityList($kernel->getMiddlewarePriority());
    }

    /**
     * @param  array<array-key, mixed>  $middlewarePriority
     * @return list<string>
     */
    private function middlewarePriorityList(array $middlewarePriority): array
    {
        return array_values(array_filter($middlewarePriority, is_string(...)));
    }
}

<?php

declare(strict_types=1);

use Capell\AccessGate\Enums\AccessAreaStatus;
use Capell\AccessGate\Enums\RegistrationStatus;
use Capell\AccessGate\Filament\Resources\AccessAreas\AccessAreaResource;
use Capell\AccessGate\Filament\Resources\AccessAreas\Pages\EditAccessArea;
use Capell\AccessGate\Filament\Resources\BrowserTokens\BrowserTokenResource;
use Capell\AccessGate\Filament\Resources\ClaimTokens\ClaimTokenResource;
use Capell\AccessGate\Filament\Resources\Events\AccessGateEventResource;
use Capell\AccessGate\Filament\Resources\Grants\GrantResource;
use Capell\AccessGate\Filament\Resources\Registrations\RegistrationResource;
use Capell\AccessGate\Frontend\Rules\AccessGateAreaStatusCondition;
use Capell\AccessGate\Health\AccessGateHealthCheck;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\BrowserToken;
use Capell\AccessGate\Models\ClaimToken;
use Capell\AccessGate\Models\Event;
use Capell\AccessGate\Models\Grant;
use Capell\AccessGate\Models\Registration;
use Capell\AccessGate\Policies\AccessAreaPolicy;
use Capell\AccessGate\Providers\AccessGateServiceProvider;
use Capell\AccessGate\Support\AccessGateDiagnosticsService;
use Capell\AccessGate\Support\AccessRequestMethodRegistry;
use Capell\AccessGate\Support\RegistrationFieldRegistry;
use Capell\AccessGate\Tests\Fixtures\Autoload\PublicRequestProviderField;
use Capell\AccessGate\Tests\Fixtures\Autoload\PublicRequestProviderMethod;
use Capell\Core\Facades\CapellCore;
use Capell\Frontend\Support\Rules\FrontendRuleConditionRegistry;
use Capell\PublicActions\Support\PublicActionHandlerRegistry;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Schema;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;

use function Pest\Laravel\artisan;

it('declares resource models pages schemas and tables', function (): void {
    CapellCore::forcePackageInstalled('capell-app/access-gate');

    expect(AccessAreaResource::getModel())->toBe(Area::class)
        ->and(RegistrationResource::getModel())->toBe(Registration::class)
        ->and(GrantResource::getModel())->toBe(Grant::class)
        ->and(ClaimTokenResource::getModel())->toBe(ClaimToken::class)
        ->and(BrowserTokenResource::getModel())->toBe(BrowserToken::class)
        ->and(AccessGateEventResource::getModel())->toBe(Event::class)
        ->and(AccessAreaResource::getPages())->toHaveKeys(['index', 'create', 'edit'])
        ->and(RegistrationResource::getPages())->toHaveKey('index')
        ->and(AccessAreaResource::shouldRegisterNavigation())->toBeTrue()
        ->and(AccessGateHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(GrantResource::getPages())->toHaveKey('index')
        ->and(ClaimTokenResource::getPages())->toHaveKey('index')
        ->and(BrowserTokenResource::getPages())->toHaveKey('index')
        ->and(AccessGateEventResource::getPages())->toHaveKey('index');

    expect(AccessAreaResource::form(Schema::make()))->toBeInstanceOf(Schema::class)
        ->and(RegistrationResource::form(Schema::make()))->toBeInstanceOf(Schema::class);

    $areaTable = AccessAreaResource::table(accessGateTableForCoverage());
    $registrationTable = RegistrationResource::table(accessGateTableForCoverage());

    expect(array_keys($areaTable->getColumns()))->toContain(
        'key',
        'name',
        'site.name',
        'status',
        'identity_mode',
        'approval_strategy',
        'registrations_count',
        'grants_count',
    )
        ->and(array_keys($areaTable->getFilters()))->toBe(['status', 'identity_mode'])
        ->and(accessGateActionNames($areaTable->getActions()))->toContain('edit')
        ->and(array_keys($registrationTable->getColumns()))->toContain(
            'email',
            'area.key',
            'status',
            'github_repository_access_status',
            'requested_host',
            'position',
            'requested_at',
            'approved_at',
            'claimed_at',
        )
        ->and(array_keys($registrationTable->getFilters()))->toBe(['access_area_id', 'status', 'requested_host'])
        ->and(accessGateActionNames($registrationTable->getActions()))->toContain(
            'approve',
            'reject',
            'resendClaim',
            'retryGithubInvites',
            'expire',
        );
});

it('reports cookie configuration failures as json doctor output', function (): void {
    config()->set('access-gate.cookies.browser_token.same_site', 'none');
    config()->set('access-gate.cookies.browser_token.secure', false);

    $command = artisan('capell:access-gate-doctor', ['--json' => true]);

    throw_if(is_int($command), RuntimeException::class, 'Expected pending artisan command.');

    $command->assertFailed()
        ->expectsOutputToContain('"status": "failed"');
});

it('reports invalid same site cookie configuration', function (): void {
    config()->set('access-gate.cookies.browser_token.same_site', 'invalid');

    $command = artisan('capell:access-gate-doctor', ['--json' => true]);

    throw_if(is_int($command), RuntimeException::class, 'Expected pending artisan command.');

    $command->assertFailed()
        ->expectsOutputToContain('"status": "failed"');
});

it('runs access area edit page header actions against the area workflow', function (): void {
    $area = Area::factory()->create([
        'status' => AccessAreaStatus::Active,
        'approval_limit' => 1,
    ]);
    $firstRegistration = Registration::factory()
        ->for($area, 'area')
        ->create(['position' => 1]);
    $secondRegistration = Registration::factory()
        ->for($area, 'area')
        ->create(['position' => 2]);

    $page = new EditAccessArea;
    $page->record = $area;

    $actions = collect(accessGateEditAreaHeaderActions($page))
        ->filter(fn (mixed $action): bool => $action instanceof Action)
        ->keyBy(fn (Action $action): string => $action->getName() ?? '');

    expect($actions->keys()->all())->toContain('pause', 'resume', 'approveNext', 'updateApprovalLimit', 'delete');

    accessGateRunAction($actions->get('pause'));

    expect($area->refresh()->status)->toBe(AccessAreaStatus::Paused);

    accessGateRunAction($actions->get('resume'));
    accessGateRunAction($actions->get('updateApprovalLimit'), ['approval_limit' => 2]);
    accessGateRunAction($actions->get('approveNext'), ['count' => 2]);

    expect($area->refresh()->status)->toBe(AccessAreaStatus::Active)
        ->and($area->approval_limit)->toBe(2)
        ->and($firstRegistration->refresh()->status)->toBe(RegistrationStatus::Approved)
        ->and($secondRegistration->refresh()->status)->toBe(RegistrationStatus::Approved);
});

it('wires provider registries middleware priority policies and rate limits for access workflows', function (): void {
    CapellCore::forcePackageInstalled(AccessGateServiceProvider::$packageName);

    $provider = new AccessGateServiceProvider(app());
    $router = resolve(Router::class);
    app()->singleton(FrontendRuleConditionRegistry::class);
    app()->singleton(PublicActionHandlerRegistry::class);

    config()->set('access-gate.registration.fields', [
        PublicRequestProviderField::class,
        stdClass::class,
        123,
    ]);
    config()->set('access-gate.registration.identity_methods', [
        PublicRequestProviderMethod::class,
        stdClass::class,
        false,
    ]);
    config()->set('access-gate.middleware.page_cache_aliases', ['frontend.cache', '', stdClass::class]);

    $router->aliasMiddleware('frontend.cache', stdClass::class);

    accessGateProviderInvoke($provider, 'registerConfiguredRegistrationFields');
    accessGateProviderInvoke($provider, 'registerConfiguredAccessRequestMethods');
    accessGateProviderInvoke($provider, 'registerPublicActionHandler');
    accessGateProviderInvoke($provider, 'registerFrontendRuleConditions');
    accessGateProviderInvoke($provider, 'registerMiddlewareAliases');
    accessGateProviderInvoke($provider, 'registerRateLimiters');
    accessGateProviderInvoke($provider, 'applyMiddlewarePriority', $router);
    accessGateProviderInvoke($provider, 'registerModels');
    accessGateProviderInvoke($provider, 'registerPolicies');
    accessGateProviderInvoke($provider, 'registerProtectedTables');

    $request = Request::create('/access/preview', Symfony\Component\HttpFoundation\Request::METHOD_POST, ['email' => 'EDITOR@EXAMPLE.TEST'], server: ['REMOTE_ADDR' => '127.0.0.1']);
    $requestRoute = new IlluminateRoute(['POST'], '/access/{area}', []);
    $requestRoute->bind($request);
    $requestRoute->setParameter('area', 'preview');

    $request->setRouteResolver(fn (): IlluminateRoute => $requestRoute);
    $limiter = RateLimiter::limiter('access-gate-request');

    expect(resolve(RegistrationFieldRegistry::class)->all())->toHaveKey('provider_username')
        ->and(resolve(AccessRequestMethodRegistry::class)->all())->toHaveKey('provider')
        ->and(resolve(PublicActionHandlerRegistry::class)->all())->toHaveKey('access-gate.request')
        ->and(resolve(FrontendRuleConditionRegistry::class)->get('access_gate_area_status'))->toBeInstanceOf(AccessGateAreaStatusCondition::class)
        ->and($router->getMiddleware())->toHaveKey('access-gate')
        ->and($router->middlewarePriority)->toContain(EncryptCookies::class, 'access-gate', stdClass::class)
        ->and(resolve(AccessGateDiagnosticsService::class)->pageCacheAliases())->toBe(['frontend.cache', stdClass::class])
        ->and(resolve(AccessGateDiagnosticsService::class)->pageCacheMiddlewarePriorityNames($router))->toContain('frontend.cache', stdClass::class)
        ->and(accessGateProviderInvoke($provider, 'middlewarePriorityList', [null, 'web', AccessGateServiceProvider::class]))->toBe(['web', AccessGateServiceProvider::class])
        ->and(accessGateProviderInvoke($provider, 'protectedTables'))->toContain('access_gate_areas', 'access_gate_events')
        ->and(CapellCore::getProtectedTables())->toContain('access_gate_areas', 'access_gate_events')
        ->and(Gate::getPolicyFor(Area::class))->toBeInstanceOf(AccessAreaPolicy::class)
        ->and($limiter)->toBeCallable()
        ->and($limiter($request))->not->toBeEmpty();
});

/**
 * @return array<array-key, mixed>
 */
function accessGateEditAreaHeaderActions(EditAccessArea $page): array
{
    $method = new ReflectionMethod(EditAccessArea::class, 'getHeaderActions');

    return $method->invoke($page);
}

function accessGateTableForCoverage(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldIgnoreMissing();
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null)->byDefault();
    $livewire->shouldReceive('getTableFilterState')->andReturn([])->byDefault();
    $livewire->shouldReceive('isTableLoaded')->andReturnTrue()->byDefault();
    $livewire->shouldReceive('getTableArguments')->andReturn([])->byDefault();

    return Table::make($livewire);
}

/**
 * @param  array<array-key, mixed>  $actions
 * @return array<int, string>
 */
function accessGateActionNames(array $actions): array
{
    return collect($actions)
        ->flatMap(fn (mixed $action): array => accessGateFlattenActionNames($action))
        ->values()
        ->all();
}

/**
 * @return array<int, string>
 */
function accessGateFlattenActionNames(mixed $action): array
{
    if ($action instanceof ActionGroup) {
        return accessGateActionNames($action->getActions());
    }

    if (is_object($action) && method_exists($action, 'getName')) {
        return [(string) $action->getName()];
    }

    return [];
}

/**
 * @param  array<array-key, mixed>  $data
 */
function accessGateRunAction(?Action $action, array $data = []): void
{
    expect($action)->toBeInstanceOf(Action::class);

    throw_unless($action instanceof Action, RuntimeException::class, 'Expected access gate action to be available.');

    $closure = $action->getActionFunction();

    expect($closure)->not->toBeNull();

    throw_if(! $closure instanceof Closure, RuntimeException::class, 'Expected access gate action to define an action closure.');

    $action->evaluate($closure, ['data' => $data]);
}

function accessGateProviderInvoke(AccessGateServiceProvider $provider, string $method, mixed ...$arguments): mixed
{
    $reflection = new ReflectionMethod(AccessGateServiceProvider::class, $method);

    return $reflection->invoke($provider, ...$arguments);
}

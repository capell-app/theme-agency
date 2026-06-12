<?php

declare(strict_types=1);

use Capell\AccessGate\Actions\CreateAccessGateBrowserTokenAction;
use Capell\AccessGate\Actions\CreateAccessGateClaimTokenAction;
use Capell\AccessGate\Actions\CreateAccessGateGrantAction;
use Capell\AccessGate\Enums\AccessAreaStatus;
use Capell\AccessGate\Enums\ApprovalStrategy;
use Capell\AccessGate\Enums\BrowserTokenStatus;
use Capell\AccessGate\Enums\GrantSubjectType;
use Capell\AccessGate\Enums\IdentityMode;
use Capell\AccessGate\Http\Middleware\AccessGateMiddleware;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\BrowserToken;
use Capell\AccessGate\Models\Grant;
use Capell\AccessGate\Models\Registration;
use Capell\AccessGate\Notifications\AccessRequestReceivedNotification;
use Capell\AccessGate\Support\AccessRequestMethodRegistry;
use Capell\AccessGate\Support\RegistrationFieldRegistry;
use Capell\AccessGate\Tests\Fixtures\Autoload\AccessGateTestUser;
use Capell\AccessGate\Tests\Fixtures\Autoload\PublicRequestProviderField;
use Capell\AccessGate\Tests\Fixtures\Autoload\PublicRequestProviderMethod;
use Capell\AccessGate\Tests\Support\FakePageCacheMiddleware;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

it('blocks protected content before the route renders', function (): void {
    $rendered = false;

    FakePageCacheMiddleware::$ran = false;

    Area::factory()->create([
        'key' => 'preview',
        'identity_mode' => IdentityMode::Hybrid,
    ]);

    Route::middleware('access-gate:preview')->get('/access-gate-test/protected', function () use (&$rendered): string {
        $rendered = true;

        return 'secret';
    });

    $this->get('/access-gate-test/protected')
        ->assertRedirect(route('capell-access-gate.request', [
            'area' => 'preview',
            'redirect' => 'http://localhost/access-gate-test/protected',
        ]));

    expect($rendered)->toBeFalse();
});

it('fails closed when a protected route references an unknown access area', function (): void {
    $rendered = false;

    Route::middleware('access-gate:missing-preview')->get('/access-gate-test/missing-area', function () use (&$rendered): string {
        $rendered = true;

        return 'secret';
    });

    $this->get('/access-gate-test/missing-area')
        ->assertRedirect(route('capell-access-gate.request', [
            'area' => 'missing-preview',
            'redirect' => 'http://localhost/access-gate-test/missing-area',
        ]));

    expect($rendered)->toBeFalse();
});

it('allows protected content when a matching area is paused', function (): void {
    Area::factory()->create([
        'key' => 'preview',
        'status' => AccessAreaStatus::Paused,
        'identity_mode' => IdentityMode::Hybrid,
    ]);

    Route::middleware('access-gate:preview')->get('/access-gate-test/paused', fn (): string => 'secret');

    $this
        ->get('/access-gate-test/paused')
        ->assertOk()
        ->assertSee('secret');
});

it('allows protected content before a scheduled access area opens', function (): void {
    Area::factory()->create([
        'key' => 'preview',
        'status' => AccessAreaStatus::Active,
        'opens_at' => now()->addHour(),
        'identity_mode' => IdentityMode::Hybrid,
    ]);

    Route::middleware('access-gate:preview')->get('/access-gate-test/before-window', fn (): string => 'secret');

    $response = $this
        ->get('/access-gate-test/before-window')
        ->assertOk()
        ->assertSee('secret');

    expect($response->baseResponse->headers->get('Cache-Control'))
        ->toContain('no-store')
        ->toContain('private');
});

it('blocks protected content during a scheduled access area window', function (): void {
    $rendered = false;

    Area::factory()->create([
        'key' => 'preview',
        'status' => AccessAreaStatus::Active,
        'opens_at' => now()->subHour(),
        'closes_at' => now()->addHour(),
        'identity_mode' => IdentityMode::Hybrid,
    ]);

    Route::middleware('access-gate:preview')->get('/access-gate-test/inside-window', function () use (&$rendered): string {
        $rendered = true;

        return 'secret';
    });

    $this->get('/access-gate-test/inside-window')
        ->assertRedirect(route('capell-access-gate.request', [
            'area' => 'preview',
            'redirect' => 'http://localhost/access-gate-test/inside-window',
        ]));

    expect($rendered)->toBeFalse();
});

it('allows protected content after a scheduled access area closes', function (): void {
    Area::factory()->create([
        'key' => 'preview',
        'status' => AccessAreaStatus::Active,
        'opens_at' => now()->subHours(2),
        'closes_at' => now()->subHour(),
        'identity_mode' => IdentityMode::Hybrid,
    ]);

    Route::middleware('access-gate:preview')->get('/access-gate-test/after-window', fn (): string => 'secret');

    $response = $this
        ->get('/access-gate-test/after-window')
        ->assertOk()
        ->assertSee('secret');

    expect($response->baseResponse->headers->get('Cache-Control'))
        ->toContain('no-store')
        ->toContain('private');
});

it('blocks protected content when the active access area is assigned to the current site', function (): void {
    defineAccessGateSiteTables();
    defineAccessGateSiteDomain(siteId: 1, domain: 'example.test');

    $rendered = false;

    Area::factory()->create([
        'site_id' => 1,
        'key' => 'preview',
        'status' => AccessAreaStatus::Active,
        'identity_mode' => IdentityMode::Hybrid,
    ]);

    Route::middleware('access-gate:preview')->get('/access-gate-test/site-assigned', function () use (&$rendered): string {
        $rendered = true;

        return 'secret';
    });

    $this->get('http://example.test/access-gate-test/site-assigned')
        ->assertRedirect(route('capell-access-gate.request', [
            'area' => 'preview',
            'redirect' => 'http://example.test/access-gate-test/site-assigned',
        ]));

    expect($rendered)->toBeFalse();
});

it('fails closed when a protected route has no access area for the current site', function (): void {
    defineAccessGateSiteTables();
    defineAccessGateSiteDomain(siteId: 1, domain: 'example.test');

    $rendered = false;

    Area::factory()->create([
        'site_id' => 2,
        'key' => 'preview',
        'status' => AccessAreaStatus::Active,
        'identity_mode' => IdentityMode::Hybrid,
    ]);

    Route::middleware('access-gate:preview')->get('/access-gate-test/site-mismatch', function () use (&$rendered): string {
        $rendered = true;

        return 'secret';
    });

    $this->get('http://example.test/access-gate-test/site-mismatch')
        ->assertRedirect(route('capell-access-gate.request', [
            'area' => 'preview',
            'redirect' => 'http://example.test/access-gate-test/site-mismatch',
        ]));

    expect($rendered)->toBeFalse();
});

it('reports the resolved access area when status checks are denied', function (): void {
    Area::factory()->create([
        'key' => 'preview',
        'status' => AccessAreaStatus::Active,
        'identity_mode' => IdentityMode::Hybrid,
    ]);

    $this
        ->getJson(route('capell-access-gate.status', ['area' => 'preview']))
        ->assertOk()
        ->assertJson([
            'allowed' => false,
        ])
        ->assertJsonMissing([
            'area' => 'preview',
        ]);
});

it('allows guest browser tokens and marks protected responses private', function (): void {
    $area = Area::factory()->create([
        'key' => 'preview',
        'identity_mode' => IdentityMode::Hybrid,
    ]);

    $grant = Grant::factory()->for($area, 'area')->create();
    $issuedToken = resolve(CreateAccessGateBrowserTokenAction::class)->handle($grant);

    Route::middleware('access-gate:preview')->get('/access-gate-test/guest', fn (): string => 'secret');

    $this
        ->withUnencryptedCookie(accessGateBrowserTokenCookieName(), $issuedToken->plainTextToken)
        ->get('/access-gate-test/guest')
        ->assertOk()
        ->assertSee('secret')
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('Pragma', 'no-cache')
        ->assertHeader('Expires', '0');
});

it('rejects revoked browser tokens', function (): void {
    $area = Area::factory()->create([
        'key' => 'preview',
        'identity_mode' => IdentityMode::Hybrid,
    ]);

    $grant = Grant::factory()->for($area, 'area')->create();
    $issuedToken = resolve(CreateAccessGateBrowserTokenAction::class)->handle($grant);
    $issuedToken->token->forceFill([
        'status' => BrowserTokenStatus::Revoked,
        'revoked_at' => now(),
    ])->save();

    Route::middleware('access-gate:preview')->get('/access-gate-test/revoked', fn (): string => 'secret');

    $this
        ->withUnencryptedCookie(accessGateBrowserTokenCookieName(), $issuedToken->plainTextToken)
        ->get('/access-gate-test/revoked')
        ->assertRedirect(route('capell-access-gate.request', [
            'area' => 'preview',
            'redirect' => 'http://localhost/access-gate-test/revoked',
        ]));
});

it('allows authenticated user grants', function (): void {
    $area = Area::factory()->create([
        'key' => 'preview',
        'identity_mode' => IdentityMode::Authenticated,
    ]);

    $user = new AccessGateTestUser;
    $user->forceFill(['id' => 123]);

    resolve(CreateAccessGateGrantAction::class)->handle(
        area: $area,
        subjectType: GrantSubjectType::User,
        userId: 123,
    );

    Route::middleware('access-gate:preview')->get('/access-gate-test/authenticated', fn (): string => 'secret');

    $this
        ->actingAs($user)
        ->get('/access-gate-test/authenticated')
        ->assertOk()
        ->assertSee('secret');
});

it('allows authenticated users with approved email grants', function (): void {
    $area = Area::factory()->create([
        'key' => 'preview',
        'identity_mode' => IdentityMode::Authenticated,
    ]);

    $user = new AccessGateTestUser;
    $user->forceFill([
        'id' => 123,
        'email' => 'mona@example.test',
    ]);

    resolve(CreateAccessGateGrantAction::class)->handle(
        area: $area,
        subjectType: GrantSubjectType::Email,
        email: 'mona@example.test',
    );

    Route::middleware('access-gate:preview')->get('/access-gate-test/email-grant', fn (): string => 'secret');

    $this
        ->actingAs($user)
        ->get('/access-gate-test/email-grant')
        ->assertOk()
        ->assertSee('secret');
});

it('allows configured public allowlist paths without a grant', function (): void {
    Area::factory()->create([
        'key' => 'preview',
        'identity_mode' => IdentityMode::Hybrid,
        'public_allowlist' => ['access-gate-test/public/*'],
    ]);

    Route::middleware('access-gate:preview')->get('/access-gate-test/public/info', fn (): string => 'public');

    $this
        ->get('/access-gate-test/public/info')
        ->assertOk()
        ->assertSee('public');
});

it('renders and stores configured public request fields', function (): void {
    Notification::fake();

    $area = Area::factory()->create([
        'key' => 'preview',
        'name' => 'Preview',
    ]);

    resolve(RegistrationFieldRegistry::class)->register(new PublicRequestProviderField);

    $this->get(route('capell-access-gate.request', ['area' => $area->key]))
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertSee('Provider username');

    $this->post(route('capell-access-gate.request.store', ['area' => $area->key]), [
        'email' => 'mona@example.test',
        'provider_username' => 'octocat',
        'requested_url' => 'https://example.test/preview',
    ])->assertRedirect(route('capell-access-gate.request', ['area' => $area->key]));

    $registration = Registration::query()->where('email_normalized', 'mona@example.test')->firstOrFail();

    expect($registration->field_values['provider_username']['value'] ?? null)->toBe('octocat');
    Notification::assertSentOnDemand(AccessRequestReceivedNotification::class);
});

it('renders host application access request methods after the email request form', function (): void {
    $area = Area::factory()->create([
        'key' => 'preview',
        'name' => 'Preview',
    ]);

    resolve(AccessRequestMethodRegistry::class)->register(new PublicRequestProviderMethod);

    $this->get(route('capell-access-gate.request', [
        'area' => $area->key,
        'redirect' => 'https://example.test/preview',
    ]))
        ->assertOk()
        ->assertSee('Continue with Provider')
        ->assertSee('Request Access')
        ->assertSee('or')
        ->assertSee('Email address');
});

it('can disable the package email form while leaving host application methods available', function (): void {
    config()->set('access-gate.registration.methods.email.enabled', false);

    $area = Area::factory()->create([
        'key' => 'preview',
        'name' => 'Preview',
    ]);

    resolve(AccessRequestMethodRegistry::class)->register(new PublicRequestProviderMethod);

    $this->get(route('capell-access-gate.request', ['area' => $area->key]))
        ->assertOk()
        ->assertSee('Continue with Provider')
        ->assertDontSee('Email address');

    $this->post(route('capell-access-gate.request.store', ['area' => $area->key]), [
        'email' => 'mona@example.test',
    ])->assertSessionHasErrors('email');
});

it('throttles public email requests by area, email, and ip address', function (): void {
    Notification::fake();

    $area = Area::factory()->create([
        'key' => 'preview',
    ]);

    for ($requestAttempt = 1; $requestAttempt <= 6; $requestAttempt++) {
        $this->post(route('capell-access-gate.request.store', ['area' => $area->key]), [
            'email' => 'mona@example.test',
        ])->assertRedirect(route('capell-access-gate.request', ['area' => $area->key]));
    }

    $this->post(route('capell-access-gate.request.store', ['area' => $area->key]), [
        'email' => 'mona@example.test',
    ])->assertTooManyRequests();
});

it('does not trust posted user ids on public access requests', function (): void {
    Notification::fake();

    $area = Area::factory()->create([
        'key' => 'preview',
        'identity_mode' => IdentityMode::Authenticated,
        'approval_strategy' => ApprovalStrategy::AutoApprove,
    ]);

    $this->post(route('capell-access-gate.request.store', ['area' => $area->key]), [
        'email' => 'mona@example.test',
        'user_id' => 123,
    ])->assertRedirect(route('capell-access-gate.request', ['area' => $area->key]));

    expect(Registration::query()->firstOrFail()->user_id)->toBeNull()
        ->and(Grant::query()->where('subject_type', GrantSubjectType::User->value)->exists())->toBeFalse()
        ->and(Grant::query()->where('subject_type', GrantSubjectType::Email->value)->exists())->toBeTrue();
});

it('uses the authenticated email when creating authenticated-mode requests', function (): void {
    Notification::fake();

    $area = Area::factory()->create([
        'key' => 'preview',
        'identity_mode' => IdentityMode::Authenticated,
        'approval_strategy' => ApprovalStrategy::AutoApprove,
    ]);

    $user = new AccessGateTestUser;
    $user->forceFill([
        'id' => 123,
        'email' => 'real@example.test',
    ]);

    $this
        ->actingAs($user)
        ->post(route('capell-access-gate.request.store', ['area' => $area->key]), [
            'email' => 'attacker@example.test',
        ])
        ->assertRedirect(route('capell-access-gate.request', ['area' => $area->key]));

    $registration = Registration::query()->firstOrFail();

    expect($registration->email)->toBe('real@example.test')
        ->and($registration->user_id)->toBe(123)
        ->and(Grant::query()->where('subject_type', GrantSubjectType::User->value)->where('subject_id', '123')->exists())->toBeTrue();
});

it('runs access gate before route-level frontend cache middleware', function (): void {
    $rendered = false;

    FakePageCacheMiddleware::$ran = false;
    FakePageCacheMiddleware::$sawProtectedRequest = false;

    $router = resolve(Router::class);
    $router->aliasMiddleware('frontend.cache', FakePageCacheMiddleware::class);

    $middlewarePriority = collect([AccessGateMiddleware::class, 'access-gate', FakePageCacheMiddleware::class])
        ->merge($router->middlewarePriority)
        ->unique()
        ->values()
        ->all();
    $router->middlewarePriority = $middlewarePriority;
    resolve(HttpKernel::class)->setMiddlewarePriority($middlewarePriority);

    Area::factory()->create([
        'key' => 'preview',
        'identity_mode' => IdentityMode::Hybrid,
    ]);

    Route::middleware(['web', 'frontend.cache', 'access-gate:preview'])
        ->get('/access-gate-test/frontend-cache-priority', function () use (&$rendered): string {
            $rendered = true;

            return 'secret';
        });

    $this
        ->get('/access-gate-test/frontend-cache-priority')
        ->assertRedirect(route('capell-access-gate.request', [
            'area' => 'preview',
            'redirect' => 'http://localhost/access-gate-test/frontend-cache-priority',
        ]))
        ->assertDontSee('cached secret');

    expect($rendered)->toBeFalse()
        ->and(FakePageCacheMiddleware::$ran)->toBeFalse();
});

it('marks allowed protected requests so compatible frontend cache middleware can skip reads and writes', function (): void {
    FakePageCacheMiddleware::$ran = false;
    FakePageCacheMiddleware::$sawProtectedRequest = false;

    $router = resolve(Router::class);
    $router->aliasMiddleware('frontend.cache', FakePageCacheMiddleware::class);

    $middlewarePriority = collect([AccessGateMiddleware::class, 'access-gate', FakePageCacheMiddleware::class])
        ->merge($router->middlewarePriority)
        ->unique()
        ->values()
        ->all();
    $router->middlewarePriority = $middlewarePriority;
    resolve(HttpKernel::class)->setMiddlewarePriority($middlewarePriority);

    $area = Area::factory()->create([
        'key' => 'preview',
        'identity_mode' => IdentityMode::Hybrid,
    ]);
    $grant = Grant::factory()->for($area, 'area')->create();
    $issuedToken = resolve(CreateAccessGateBrowserTokenAction::class)->handle($grant);

    Route::middleware(['web', 'frontend.cache', 'access-gate:preview'])
        ->get('/access-gate-test/frontend-cache-allowed', fn (): string => 'secret');

    $this
        ->withUnencryptedCookie(accessGateBrowserTokenCookieName(), $issuedToken->plainTextToken)
        ->get('/access-gate-test/frontend-cache-allowed')
        ->assertOk()
        ->assertSee('secret')
        ->assertDontSee('cached secret')
        ->assertHeader('Cache-Control', 'no-store, private');

    expect(FakePageCacheMiddleware::$ran)->toBeTrue()
        ->and(FakePageCacheMiddleware::$sawProtectedRequest)->toBeTrue();
});

it('claims access with a one-time token and stores the browser token cookie', function (): void {
    $area = Area::factory()->create([
        'claim_url_hosts' => ['example.test'],
    ]);
    $registration = Registration::factory()
        ->for($area, 'area')
        ->create(['requested_url' => 'https://example.test/preview']);
    $grant = Grant::factory()
        ->for($area, 'area')
        ->for($registration, 'registration')
        ->create();
    $issuedClaimToken = resolve(CreateAccessGateClaimTokenAction::class)->handle($grant);

    $this
        ->withHeader('User-Agent', 'AccessGateTest/1.0')
        ->get(route('capell-access-gate.claim', ['token' => $issuedClaimToken->plainTextToken]))
        ->assertOk()
        ->assertSee(__('capell-access-gate::public.claim_succeeded.title'))
        ->assertSee('href="https://example.test/preview"', false)
        ->assertCookie(accessGateBrowserTokenCookieName());

    $browserToken = BrowserToken::query()->firstOrFail();

    expect($browserToken->ip_hash)->toBe(hash('sha256', '127.0.0.1'))
        ->and($browserToken->user_agent)->toBe('AccessGateTest/1.0');
});

it('lands claimed users on the configured area landing url', function (): void {
    $area = Area::factory()->create([
        'claim_url_hosts' => ['example.test'],
        'claim_landing_url' => '/account',
    ]);
    $registration = Registration::factory()
        ->for($area, 'area')
        ->create(['requested_url' => 'https://example.test/preview']);
    $grant = Grant::factory()
        ->for($area, 'area')
        ->for($registration, 'registration')
        ->create();
    $issuedClaimToken = resolve(CreateAccessGateClaimTokenAction::class)->handle($grant);

    $this->get(route('capell-access-gate.claim', ['token' => $issuedClaimToken->plainTextToken]))
        ->assertOk()
        ->assertSee('href="' . url('/account') . '"', false)
        ->assertCookie(accessGateBrowserTokenCookieName());
});

it('does not send claimed users to untrusted requested urls', function (): void {
    $area = Area::factory()->create([
        'claim_url_hosts' => ['example.test'],
    ]);
    $registration = Registration::factory()
        ->for($area, 'area')
        ->create(['requested_url' => 'https://attacker.test/preview']);
    $grant = Grant::factory()
        ->for($area, 'area')
        ->for($registration, 'registration')
        ->create();
    $issuedClaimToken = resolve(CreateAccessGateClaimTokenAction::class)->handle($grant);

    $this->get(route('capell-access-gate.claim', ['token' => $issuedClaimToken->plainTextToken]))
        ->assertOk()
        ->assertSee('href="' . url('/') . '"', false)
        ->assertDontSee('attacker.test');
});

it('does not send claimed users to non-http requested urls on trusted hosts', function (): void {
    $area = Area::factory()->create([
        'claim_url_hosts' => ['localhost'],
    ]);
    $registration = Registration::factory()
        ->for($area, 'area')
        ->create(['requested_url' => 'javascript://localhost/preview']);
    $grant = Grant::factory()
        ->for($area, 'area')
        ->for($registration, 'registration')
        ->create();
    $issuedClaimToken = resolve(CreateAccessGateClaimTokenAction::class)->handle($grant);

    $this->get(route('capell-access-gate.claim', ['token' => $issuedClaimToken->plainTextToken]))
        ->assertOk()
        ->assertSee('href="' . url('/') . '"', false)
        ->assertDontSee('javascript:', false);
});

it('revokes the local browser token on access gate logout', function (): void {
    $area = Area::factory()->create([
        'key' => 'preview',
    ]);
    $grant = Grant::factory()->for($area, 'area')->create();
    $issuedToken = resolve(CreateAccessGateBrowserTokenAction::class)->handle($grant);

    $this
        ->withCookie(accessGateBrowserTokenCookieName(), $issuedToken->plainTextToken)
        ->post(route('capell-access-gate.logout', ['area' => $area->key]))
        ->assertRedirect(route('capell-access-gate.request', ['area' => $area->key]))
        ->assertCookieExpired(accessGateBrowserTokenCookieName());

    expect(BrowserToken::query()->firstOrFail()->status)->toBe(BrowserTokenStatus::Revoked);
});

it('throttles access gate logout attempts', function (): void {
    $route = Route::getRoutes()->getByName('capell-access-gate.logout');

    expect($route)->not->toBeNull()
        ->and($route->gatherMiddleware())->toContain('throttle:access-gate-logout');
});

it('resolves public access request areas within the current site context', function (): void {
    defineAccessGateSiteTables();
    defineAccessGateSiteDomain(1, 'localhost');
    defineAccessGateSiteDomain(2, 'hidden.test');

    $localArea = Area::factory()->create([
        'key' => 'preview',
        'site_id' => 1,
        'name' => 'Local Preview',
        'identity_mode' => IdentityMode::Hybrid,
    ]);
    Area::factory()->create([
        'key' => 'hidden-preview',
        'site_id' => 2,
        'name' => 'Hidden Preview',
        'identity_mode' => IdentityMode::Hybrid,
    ]);

    $this->get('http://localhost/access/request/preview')
        ->assertOk()
        ->assertSee('Local Preview')
        ->assertDontSee('Hidden Preview');

    $this->post('http://localhost/access/request/preview', [
        'email' => 'ben@example.test',
    ])->assertRedirect(route('capell-access-gate.request', ['area' => 'preview']));

    expect(Registration::query()->firstOrFail()->access_area_id)->toBe($localArea->getKey());

    $this->get('http://localhost/access/request/hidden-preview')->assertNotFound();
    $this->post('http://localhost/access/request/hidden-preview', [
        'email' => 'hidden@example.test',
    ])->assertNotFound();

    expect(Registration::query()->count())->toBe(1);
});

it('does not revoke another site access gate browser token through the current site logout route', function (): void {
    defineAccessGateSiteTables();
    defineAccessGateSiteDomain(1, 'localhost');
    defineAccessGateSiteDomain(2, 'hidden.test');

    Area::factory()->create([
        'key' => 'preview',
        'site_id' => 1,
    ]);
    $hiddenArea = Area::factory()->create([
        'key' => 'hidden-preview',
        'site_id' => 2,
    ]);
    $hiddenGrant = Grant::factory()->for($hiddenArea, 'area')->create();
    $hiddenToken = resolve(CreateAccessGateBrowserTokenAction::class)->handle($hiddenGrant);

    $this
        ->withCookie(accessGateBrowserTokenCookieName(), $hiddenToken->plainTextToken)
        ->post('http://localhost/access/logout/hidden-preview')
        ->assertNotFound();

    expect(BrowserToken::query()->firstOrFail()->status)->toBe(BrowserTokenStatus::Active);
});

function defineAccessGateSiteTables(): void
{
    if (! Schema::hasTable('sites')) {
        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('site_domains')) {
        Schema::create('site_domains', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->string('domain')->nullable();
            $table->string('scheme', 12)->nullable();
            $table->string('path', 125)->nullable();
            $table->boolean('status')->index()->default(true);
            $table->boolean('default')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }
}

function defineAccessGateSiteDomain(int $siteId, string $domain): void
{
    DB::table('sites')->insertOrIgnore([
        'id' => $siteId,
        'name' => 'Site ' . $siteId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('site_domains')->insert([
        'site_id' => $siteId,
        'domain' => $domain,
        'scheme' => 'http',
        'path' => '/',
        'status' => true,
        'default' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function accessGateBrowserTokenCookieName(): string
{
    $cookieName = config('access-gate.cookies.browser_token.name');

    throw_unless(is_string($cookieName) && $cookieName !== '', RuntimeException::class, 'Expected access gate browser token cookie name.');

    return $cookieName;
}

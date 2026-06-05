<?php

declare(strict_types=1);

use Capell\Core\Database\Factories\UserFactory;
use Capell\PasswordPolicy\Filament\Pages\ForcedPasswordChangePage;
use Capell\PasswordPolicy\Http\Middleware\EnsurePasswordPolicyCompliance;
use Capell\PasswordPolicy\Settings\PasswordPolicySettings;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

uses()->group('password-policy');

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

function enablePasswordPolicyForcedChanges(): void
{
    $settings = PasswordPolicySettings::instance();
    $settings->force_change_enabled = true;
    $settings->save();
}

function makePasswordPolicyMiddlewareRequest(string $uri, Model $user, ?Route $route = null): Request
{
    $request = Request::create($uri);
    $request->setUserResolver(fn (?string $guard = null): Model => $user);

    if ($route instanceof Route) {
        $request->setRouteResolver(fn (): Route => $route);
    }

    return $request;
}

function runPasswordPolicyMiddleware(Request $request): SymfonyResponse
{
    return (new EnsurePasswordPolicyCompliance)->handle(
        $request,
        fn (Request $handledRequest): SymfonyResponse => new Response('next'),
    );
}

function makePasswordPolicyLogoutRoute(): Route
{
    return (new Route(['POST'], 'admin/logout', fn (): SymfonyResponse => new Response('logout')))->name('filament.admin.auth.logout');
}

it('redirects non-compliant users to the forced password change page', function (): void {
    enablePasswordPolicyForcedChanges();

    $user = UserFactory::new()->create([
        'must_change_password' => true,
    ]);

    $response = runPasswordPolicyMiddleware(
        makePasswordPolicyMiddlewareRequest('/admin/pages', $user),
    );

    expect($response->isRedirect(ForcedPasswordChangePage::getUrl()))->toBeTrue();
});

it('allows non-compliant users to access the forced password change page', function (): void {
    enablePasswordPolicyForcedChanges();

    $user = UserFactory::new()->create([
        'must_change_password' => true,
    ]);
    $changePasswordPath = parse_url(ForcedPasswordChangePage::getUrl(), PHP_URL_PATH);

    throw_unless(is_string($changePasswordPath), RuntimeException::class, 'Expected the forced password change URL to contain a path.');

    $response = runPasswordPolicyMiddleware(
        makePasswordPolicyMiddlewareRequest($changePasswordPath, $user),
    );

    expect($response->getContent())->toBe('next');
});

it('allows non-compliant users to log out', function (): void {
    enablePasswordPolicyForcedChanges();

    $user = UserFactory::new()->create([
        'must_change_password' => true,
    ]);

    $response = runPasswordPolicyMiddleware(
        makePasswordPolicyMiddlewareRequest('/admin/logout', $user, makePasswordPolicyLogoutRoute()),
    );

    expect($response->getContent())->toBe('next');
});

it('does nothing when the authenticated user is compliant', function (): void {
    enablePasswordPolicyForcedChanges();

    $user = UserFactory::new()->create([
        'must_change_password' => false,
    ]);

    $response = runPasswordPolicyMiddleware(
        makePasswordPolicyMiddlewareRequest('/admin/pages', $user),
    );

    expect($response->getContent())->toBe('next');
});

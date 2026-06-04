<?php

declare(strict_types=1);

use Capell\LoginAudit\Models\LoginAudit;
use Capell\Tests\Fixtures\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Rappasoft\LaravelAuthenticationLog\LaravelAuthenticationLogServiceProvider;

beforeEach(function (): void {
    app()->register(LaravelAuthenticationLogServiceProvider::class);
});

function bindLoginAuditAuthEventRequest(string $ipAddress, string $userAgent): void
{
    app()->instance('request', Request::create(
        uri: '/login',
        method: Symfony\Component\HttpFoundation\Request::METHOD_POST,
        server: [
            'REMOTE_ADDR' => $ipAddress,
            'HTTP_USER_AGENT' => $userAgent,
        ],
    ));
}

it('records successful login audit rows from real Laravel login events', function (): void {
    config()->set('authentication-log.notifications.new-device.enabled', false);
    config()->set('authentication-log.notifications.new-device.location', false);

    $user = User::factory()->create();
    bindLoginAuditAuthEventRequest('198.51.100.44', 'Capell Auth Browser/1.0');

    Event::dispatch(new Login('web', $user, false));

    $audit = LoginAudit::query()->first();

    expect($audit)->not->toBeNull()
        ->and($audit?->authenticatable_type)->toBe($user->getMorphClass())
        ->and($audit?->authenticatable_id)->toBe($user->getKey())
        ->and($audit?->ip_address)->toBe('198.51.100.44')
        ->and($audit?->user_agent)->toBe('Capell Auth Browser/1.0')
        ->and($audit?->login_successful)->toBeTrue()
        ->and($audit?->login_at)->not->toBeNull();
});

it('records failed login audit rows from real Laravel failed events', function (): void {
    config()->set('authentication-log.notifications.failed-login.enabled', false);
    config()->set('authentication-log.notifications.failed-login.location', false);

    $user = User::factory()->create();
    bindLoginAuditAuthEventRequest('203.0.113.77', 'Capell Failed Browser/1.0');

    Event::dispatch(new Failed('web', $user, ['email' => $user->email]));

    $audit = LoginAudit::query()->first();

    expect($audit)->not->toBeNull()
        ->and($audit?->authenticatable_type)->toBe($user->getMorphClass())
        ->and($audit?->authenticatable_id)->toBe($user->getKey())
        ->and($audit?->ip_address)->toBe('203.0.113.77')
        ->and($audit?->user_agent)->toBe('Capell Failed Browser/1.0')
        ->and($audit?->login_successful)->toBeFalse()
        ->and($audit?->login_at)->not->toBeNull();
});

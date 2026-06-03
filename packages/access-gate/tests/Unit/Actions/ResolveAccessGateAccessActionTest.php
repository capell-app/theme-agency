<?php

declare(strict_types=1);

use Capell\AccessGate\Actions\ResolveAccessGateAccessAction;
use Capell\AccessGate\Enums\AccessAreaStatus;
use Capell\AccessGate\Enums\BrowserTokenStatus;
use Capell\AccessGate\Enums\GrantStatus;
use Capell\AccessGate\Enums\GrantSubjectType;
use Capell\AccessGate\Enums\IdentityMode;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\BrowserToken;
use Capell\AccessGate\Models\Grant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;

it('allows requests that match an access area public allowlist before requiring a grant', function (): void {
    Area::factory()->create([
        'key' => 'preview',
        'identity_mode' => IdentityMode::Authenticated,
        'public_allowlist' => [
            ['host' => 'example.test', 'path' => 'preview/public*'],
        ],
    ]);

    $result = ResolveAccessGateAccessAction::run(
        Request::create('https://example.test/preview/public-page'),
        ['preview'],
    );

    expect($result->allowed)->toBeTrue()
        ->and($result->area?->key)->toBe('preview')
        ->and($result->grant)->toBeNull()
        ->and($result->browserToken)->toBeNull();
});

it('treats bare public allowlist strings as path patterns only', function (): void {
    Area::factory()->create([
        'key' => 'preview',
        'identity_mode' => IdentityMode::Authenticated,
        'public_allowlist' => [
            'example.test',
            'https://example.test/preview/public-page',
            'preview/public*',
        ],
    ]);

    $hostOnlyResult = ResolveAccessGateAccessAction::run(
        Request::create('https://example.test/preview/private-page'),
        ['preview'],
    );
    $pathResult = ResolveAccessGateAccessAction::run(
        Request::create('https://example.test/preview/public-page'),
        ['preview'],
    );

    expect($hostOnlyResult->allowed)->toBeFalse()
        ->and($pathResult->allowed)->toBeTrue();
});

it('allows exact public allowlist urls through array entries', function (): void {
    Area::factory()->create([
        'key' => 'preview',
        'identity_mode' => IdentityMode::Authenticated,
        'public_allowlist' => [
            ['url' => 'https://example.test/preview/public-page?download=1'],
        ],
    ]);

    $matchingResult = ResolveAccessGateAccessAction::run(
        Request::create('https://example.test/preview/public-page?download=1'),
        ['preview'],
    );
    $mismatchedResult = ResolveAccessGateAccessAction::run(
        Request::create('https://example.test/preview/public-page?download=2'),
        ['preview'],
    );

    expect($matchingResult->allowed)->toBeTrue()
        ->and($mismatchedResult->allowed)->toBeFalse();
});

it('resolves active authenticated grants by user id and email and rejects expired grants', function (): void {
    $area = Area::factory()->create([
        'key' => 'members',
        'identity_mode' => IdentityMode::Authenticated,
    ]);
    $userGrant = Grant::factory()->for($area, 'area')->create([
        'subject_type' => GrantSubjectType::User,
        'subject_id' => '42',
        'email' => null,
    ]);
    Grant::factory()->for($area, 'area')->create([
        'subject_type' => GrantSubjectType::Email,
        'subject_id' => 'expired@example.test',
        'email' => 'expired@example.test',
        'expires_at' => now()->subMinute(),
    ]);
    $emailGrant = Grant::factory()->for($area, 'area')->create([
        'subject_type' => GrantSubjectType::Email,
        'subject_id' => 'reader@example.test',
        'email' => 'reader@example.test',
    ]);

    $userRequest = accessGateRequestWithUser('https://example.test/members', new class extends User
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        public int $id = 42;

        public string $email = 'ignored@example.test';
    });
    $emailRequest = accessGateRequestWithUser('https://example.test/members', new class extends User
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        public string $email = 'reader@example.test';

        public function getAuthIdentifier(): mixed
        {
            return null;
        }
    });
    $expiredRequest = accessGateRequestWithUser('https://example.test/members', new class extends User
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        public string $email = 'expired@example.test';

        public function getAuthIdentifier(): mixed
        {
            return null;
        }
    });

    $userResult = ResolveAccessGateAccessAction::run($userRequest, ['members']);
    $emailResult = ResolveAccessGateAccessAction::run($emailRequest, ['members']);
    $expiredResult = ResolveAccessGateAccessAction::run($expiredRequest, ['members']);

    expect($userResult->allowed)->toBeTrue()
        ->and($userResult->grant?->is($userGrant))->toBeTrue()
        ->and($emailResult->allowed)->toBeTrue()
        ->and($emailResult->grant?->is($emailGrant))->toBeTrue()
        ->and($expiredResult->allowed)->toBeFalse()
        ->and($expiredResult->area?->is($area))->toBeTrue();
});

it('resolves active browser tokens for guest-link areas and updates last-used time', function (): void {
    $area = Area::factory()->create([
        'key' => 'guest-preview',
        'identity_mode' => IdentityMode::GuestLink,
    ]);
    $grant = Grant::factory()->for($area, 'area')->create();
    $plainToken = 'plain-browser-token';
    $browserToken = BrowserToken::factory()
        ->for($area, 'area')
        ->for($grant, 'grant')
        ->create([
            'token_hash' => hash('sha256', $plainToken),
            'status' => BrowserTokenStatus::Active,
            'last_used_at' => null,
        ]);
    BrowserToken::factory()
        ->for($area, 'area')
        ->for($grant, 'grant')
        ->create([
            'token_hash' => hash('sha256', 'revoked-token'),
            'status' => BrowserTokenStatus::Revoked,
            'revoked_at' => now(),
        ]);

    $request = Request::create('https://example.test/guest-preview');
    $request->cookies->set('capell_access_gate_browser_token', $plainToken);

    $result = ResolveAccessGateAccessAction::run($request, ['guest-preview']);

    expect($result->allowed)->toBeTrue()
        ->and($result->grant?->is($grant))->toBeTrue()
        ->and($result->browserToken?->is($browserToken))->toBeTrue()
        ->and($browserToken->refresh()->last_used_at)->not->toBeNull();
});

it('allows scheduled inactive areas while returning the scheduled area for gate messaging', function (): void {
    $area = Area::factory()->create([
        'key' => 'launch',
        'status' => AccessAreaStatus::Active,
        'opens_at' => now()->addDay(),
        'closes_at' => now()->addDays(7),
    ]);

    $result = ResolveAccessGateAccessAction::run(
        Request::create('https://example.test/launch'),
        ['launch'],
    );

    expect($result->allowed)->toBeTrue()
        ->and($result->area?->is($area))->toBeTrue()
        ->and($result->grant)->toBeNull();
});

it('denies missing areas and areas with only inactive grants', function (): void {
    $area = Area::factory()->create([
        'key' => 'private',
        'identity_mode' => IdentityMode::Authenticated,
    ]);
    Grant::factory()->for($area, 'area')->create([
        'subject_type' => GrantSubjectType::Email,
        'subject_id' => 'reader@example.test',
        'email' => 'reader@example.test',
        'status' => GrantStatus::Revoked,
        'revoked_at' => now(),
    ]);

    $missingResult = ResolveAccessGateAccessAction::run(
        Request::create('https://example.test/missing'),
        ['missing'],
    );
    $revokedGrantResult = ResolveAccessGateAccessAction::run(
        accessGateRequestWithUser('https://example.test/private', new class extends User
        {
            /** @use HasFactory<Factory<static>> */
            use HasFactory;

            public string $email = 'reader@example.test';

            public function getAuthIdentifier(): mixed
            {
                return null;
            }
        }),
        ['private'],
    );

    expect($missingResult->allowed)->toBeFalse()
        ->and($missingResult->area)->toBeNull()
        ->and($revokedGrantResult->allowed)->toBeFalse()
        ->and($revokedGrantResult->area?->is($area))->toBeTrue();
});

function accessGateRequestWithUser(string $uri, User $user): Request
{
    $request = Request::create($uri);
    $request->setUserResolver(static fn (): User => $user);

    return $request;
}

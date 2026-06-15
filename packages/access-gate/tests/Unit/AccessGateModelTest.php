<?php

declare(strict_types=1);

use Capell\AccessGate\Enums\AccessAreaStatus;
use Capell\AccessGate\Enums\ApprovalStrategy;
use Capell\AccessGate\Enums\GrantSubjectType;
use Capell\AccessGate\Enums\IdentityMode;
use Capell\AccessGate\Enums\RegistrationPolicy;
use Capell\AccessGate\Enums\TokenPolicy;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\BrowserToken;
use Capell\AccessGate\Models\ClaimToken;
use Capell\AccessGate\Models\Event;
use Capell\AccessGate\Models\Grant;
use Capell\AccessGate\Models\Registration;
use Capell\AccessGate\Support\AccessGateDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('respects the configured access gate database connection on models', function (): void {
    Config::set('access-gate.connection', 'access_gate_testing');

    expect((new Area)->getConnectionName())->toBe('access_gate_testing')
        ->and((new ClaimToken)->getConnectionName())->toBe('access_gate_testing')
        ->and((new BrowserToken)->getConnectionName())->toBe('access_gate_testing');
});

it('runs access gate transactions on the configured database connection', function (): void {
    Config::set('database.connections.access_gate_testing', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);
    Config::set('access-gate.connection', 'access_gate_testing');

    $transactionLevels = AccessGateDatabase::transaction(fn (): array => [
        'default' => DB::connection()->transactionLevel(),
        'access_gate' => DB::connection('access_gate_testing')->transactionLevel(),
    ]);

    expect($transactionLevels)->toBe([
        'default' => 1,
        'access_gate' => 1,
    ]);
});

it('keeps token persistence limited to hashed token columns', function (): void {
    expect(Schema::hasColumn('access_gate_claim_tokens', 'token_hash'))->toBeTrue()
        ->and(Schema::hasColumn('access_gate_claim_tokens', 'token'))->toBeFalse()
        ->and(Schema::hasColumn('access_gate_claim_tokens', 'raw_token'))->toBeFalse()
        ->and(Schema::hasColumn('access_gate_browser_tokens', 'token_hash'))->toBeTrue()
        ->and(Schema::hasColumn('access_gate_browser_tokens', 'token'))->toBeFalse()
        ->and(Schema::hasColumn('access_gate_browser_tokens', 'raw_token'))->toBeFalse()
        ->and((new ClaimToken)->getFillable())->not->toContain('token', 'raw_token', 'plain_token')
        ->and((new BrowserToken)->getFillable())->not->toContain('token', 'raw_token', 'plain_token');
});

it('casts grant subject type to a backed enum', function (): void {
    $grant = new Grant([
        'subject_type' => GrantSubjectType::Email,
    ]);

    expect($grant->subject_type)->toBe(GrantSubjectType::Email);
});

it('creates browser token factories scoped to the same area as their grant', function (): void {
    $browserToken = BrowserToken::factory()->create();

    expect($browserToken->access_area_id)->toBe($browserToken->grant->access_area_id);
});

it('covers area relationships and enum casts', function (): void {
    $area = new Area([
        'status' => AccessAreaStatus::Active,
        'identity_mode' => IdentityMode::Hybrid,
        'approval_strategy' => ApprovalStrategy::Manual,
        'registration_policy' => RegistrationPolicy::SinglePerEmail,
        'token_policy' => TokenPolicy::SingleActiveBrowserToken,
        'public_allowlist' => ['https://example.test'],
        'claim_url_hosts' => ['example.test'],
        'metadata' => ['tier' => 'partner'],
        'discount_metadata' => ['source' => 'launch'],
        'announcement_enabled' => true,
        'announcement_path_patterns' => ['extensions/*'],
    ]);

    expect($area->site()->getForeignKeyName())->toBe('site_id')
        ->and($area->registrations()->getRelated())->toBeInstanceOf(Registration::class)
        ->and($area->grants()->getRelated())->toBeInstanceOf(Grant::class)
        ->and($area->claimTokens()->getRelated())->toBeInstanceOf(ClaimToken::class)
        ->and($area->browserTokens()->getRelated())->toBeInstanceOf(BrowserToken::class)
        ->and($area->events()->getRelated())->toBeInstanceOf(Event::class)
        ->and($area->status)->toBe(AccessAreaStatus::Active)
        ->and($area->identity_mode)->toBe(IdentityMode::Hybrid)
        ->and($area->approval_strategy)->toBe(ApprovalStrategy::Manual)
        ->and($area->registration_policy)->toBe(RegistrationPolicy::SinglePerEmail)
        ->and($area->token_policy)->toBe(TokenPolicy::SingleActiveBrowserToken)
        ->and($area->public_allowlist)->toBe(['https://example.test'])
        ->and($area->claim_url_hosts)->toBe(['example.test'])
        ->and($area->metadata)->toBe(['tier' => 'partner'])
        ->and($area->discount_metadata)->toBe(['source' => 'launch'])
        ->and($area->announcement_enabled)->toBeTrue()
        ->and($area->announcement_path_patterns)->toBe(['extensions/*']);
});

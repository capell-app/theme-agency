<?php

declare(strict_types=1);

use Capell\PublicActions\Actions\BuildPublicActionIntegrationQueryAction;
use Capell\PublicActions\Actions\ListPublicActionOptionsAction;
use Capell\PublicActions\Actions\ResolvePublicActionForIntegrationTokenAction;
use Capell\PublicActions\Actions\ResolvePublicActionIntegrationTokenAction;
use Capell\PublicActions\Actions\RevokePublicActionIntegrationTokenAction;
use Capell\PublicActions\Enums\PublicActionDestinationStatus;
use Capell\PublicActions\Enums\PublicActionDispatchStatus;
use Capell\PublicActions\Enums\PublicActionIntegrationProvider;
use Capell\PublicActions\Enums\PublicActionIntegrationTokenAbility;
use Capell\PublicActions\Enums\PublicActionStatus;
use Capell\PublicActions\Enums\PublicActionSubmissionStatus;
use Capell\PublicActions\Http\Controllers\Zapier\ShowZapierAccountController;
use Capell\PublicActions\Models\PublicAction;
use Capell\PublicActions\Models\PublicActionDestination;
use Capell\PublicActions\Models\PublicActionDispatchAttempt;
use Capell\PublicActions\Models\PublicActionIntegrationToken;
use Capell\PublicActions\Models\PublicActionSubmission;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('casts statuses, structured payloads, and timestamps', function (): void {
    $action = PublicAction::factory()->create([
        'status' => PublicActionStatus::Paused,
        'payload_schema' => ['fields' => [['key' => 'email']]],
        'settings' => ['store_submissions' => true],
    ]);

    $destination = PublicActionDestination::factory()->for($action, 'action')->create([
        'status' => PublicActionDestinationStatus::Paused,
        'headers' => ['Authorization' => 'Bearer secret'],
        'settings' => ['method' => 'POST'],
    ]);

    $submission = PublicActionSubmission::factory()->for($action, 'action')->create([
        'status' => PublicActionSubmissionStatus::Handled,
        'payload' => ['email' => 'person@example.test'],
        'metadata' => ['ip_hash' => 'hashed-ip'],
    ]);

    $dispatchAttempt = PublicActionDispatchAttempt::factory()
        ->for($submission, 'submission')
        ->for($destination, 'destination')
        ->create([
            'status' => PublicActionDispatchStatus::Succeeded,
            'response_status' => 202,
            'dispatched_at' => now(),
        ]);

    $integrationToken = PublicActionIntegrationToken::factory()->create([
        'provider' => PublicActionIntegrationProvider::Zapier,
        'abilities' => ['list_actions'],
        'last_used_at' => now(),
    ]);

    expect($action->refresh()->status)->toBe(PublicActionStatus::Paused)
        ->and($action->payload_schema)->toBe(['fields' => [['key' => 'email']]])
        ->and($destination->refresh()->status)->toBe(PublicActionDestinationStatus::Paused)
        ->and($destination->headers)->toBe(['Authorization' => 'Bearer secret'])
        ->and($submission->refresh()->status)->toBe(PublicActionSubmissionStatus::Handled)
        ->and($submission->payload)->toBe(['email' => 'person@example.test'])
        ->and($dispatchAttempt->refresh()->status)->toBe(PublicActionDispatchStatus::Succeeded)
        ->and($dispatchAttempt->response_status)->toBe(202)
        ->and($dispatchAttempt->dispatched_at)->not->toBeNull()
        ->and($integrationToken->refresh()->provider)->toBe(PublicActionIntegrationProvider::Zapier)
        ->and($integrationToken->abilities)->toBe(['list_actions'])
        ->and($integrationToken->last_used_at)->not->toBeNull();
});

it('defines the expected relationships', function (): void {
    $action = PublicAction::factory()->create();
    $destination = PublicActionDestination::factory()->for($action, 'action')->create();
    $submission = PublicActionSubmission::factory()->for($action, 'action')->create();
    $dispatchAttempt = PublicActionDispatchAttempt::factory()
        ->for($submission, 'submission')
        ->for($destination, 'destination')
        ->create();

    expect($action->destinations()->first()?->is($destination))->toBeTrue()
        ->and($action->submissions()->first()?->is($submission))->toBeTrue()
        ->and($destination->action->is($action))->toBeTrue()
        ->and($destination->dispatchAttempts()->first()?->is($dispatchAttempt))->toBeTrue()
        ->and($submission->action->is($action))->toBeTrue()
        ->and($submission->dispatchAttempts()->first()?->is($dispatchAttempt))->toBeTrue()
        ->and($dispatchAttempt->submission->is($submission))->toBeTrue()
        ->and($dispatchAttempt->destination?->is($destination))->toBeTrue();
});

it('encrypts destination secrets and submission payloads at rest', function (): void {
    $destination = PublicActionDestination::factory()->create([
        'endpoint_url' => 'https://hooks.example.test/secret',
        'secret' => 'plain-secret',
        'headers' => ['Authorization' => 'Bearer plain-secret'],
    ]);

    $submission = PublicActionSubmission::factory()->create([
        'payload' => ['email' => 'private@example.test'],
    ]);

    $storedDestination = DB::table('public_action_destinations')->where('id', $destination->getKey())->first();
    $storedSubmission = DB::table('public_action_submissions')->where('id', $submission->getKey())->first();

    throw_if(! $storedDestination instanceof stdClass || ! $storedSubmission instanceof stdClass, RuntimeException::class, 'Expected encrypted public action rows to exist.');

    expect((string) $storedDestination->endpoint_url)->not->toContain('hooks.example.test')
        ->and((string) $storedDestination->secret)->not->toContain('plain-secret')
        ->and((string) $storedDestination->headers)->not->toContain('plain-secret')
        ->and((string) $storedSubmission->payload)->not->toContain('private@example.test')
        ->and($destination->refresh()->endpoint_url)->toBe('https://hooks.example.test/secret')
        ->and($destination->secret)->toBe('plain-secret')
        ->and($submission->refresh()->payload)->toBe(['email' => 'private@example.test']);
});

it('enforces action key uniqueness per site scope', function (): void {
    PublicAction::factory()->create([
        'site_scope_key' => 'global',
        'key' => 'preview-access',
    ]);

    PublicAction::factory()->create([
        'site_scope_key' => 'site:1',
        'key' => 'preview-access',
    ]);

    expect(fn (): PublicAction => PublicAction::factory()->create([
        'site_scope_key' => 'global',
        'key' => 'preview-access',
    ]))->toThrow(QueryException::class);
});

it('hashes and revokes integration tokens without storing the plain token', function (): void {
    $plainTextToken = 'capell_pa_test_token';
    $integrationToken = PublicActionIntegrationToken::factory()->create([
        'token_hash' => PublicActionIntegrationToken::hashPlainTextToken($plainTextToken),
        'revoked_at' => null,
    ]);

    $storedToken = DB::table('public_action_integration_tokens')->where('id', $integrationToken->getKey())->first();

    throw_unless($storedToken instanceof stdClass, RuntimeException::class, 'Expected public action integration token row to exist.');

    expect((string) $storedToken->token_hash)->toBe(hash('sha256', $plainTextToken))
        ->and((string) $storedToken->token_hash)->not->toBe($plainTextToken)
        ->and($integrationToken->isRevoked())->toBeFalse();

    $integrationToken->forceFill(['revoked_at' => now()])->save();

    expect($integrationToken->refresh()->isRevoked())->toBeTrue();
});

it('checks integration token abilities from its cast payload', function (): void {
    $integrationToken = PublicActionIntegrationToken::factory()->create([
        'abilities' => [
            PublicActionIntegrationTokenAbility::ListActions->value,
            PublicActionIntegrationTokenAbility::SubmitActions->value,
        ],
    ]);

    expect($integrationToken->hasAbility(PublicActionIntegrationTokenAbility::ListActions))->toBeTrue()
        ->and($integrationToken->hasAbility(PublicActionIntegrationTokenAbility::SubmitActions))->toBeTrue()
        ->and($integrationToken->hasAbility(PublicActionIntegrationTokenAbility::ReadSubmissions))->toBeFalse()
        ->and($integrationToken->site()->getRelated()->getTable())->toBe('sites');
});

it('resolves active integration tokens and scoped public actions', function (): void {
    $plainTextToken = 'cpa_test_plain_token';
    $integrationToken = PublicActionIntegrationToken::factory()->create([
        'provider' => PublicActionIntegrationProvider::Api,
        'token_hash' => PublicActionIntegrationToken::hashPlainTextToken($plainTextToken),
        'revoked_at' => null,
    ]);
    $enabledAction = PublicAction::factory()->create([
        'key' => 'enabled-api-action',
        'settings' => ['api_enabled' => true],
    ]);
    PublicAction::factory()->create([
        'key' => 'disabled-api-action',
        'settings' => ['api_enabled' => false],
    ]);

    $resolveToken = new ResolvePublicActionIntegrationTokenAction;
    $buildQuery = new BuildPublicActionIntegrationQueryAction;
    $resolveAction = new ResolvePublicActionForIntegrationTokenAction($buildQuery);

    expect($resolveToken->handle(null))->toBeNull()
        ->and($resolveToken->handle(''))->toBeNull()
        ->and($resolveToken->handle('wrong-token'))->toBeNull()
        ->and($resolveToken->handle($plainTextToken)?->is($integrationToken))->toBeTrue()
        ->and($integrationToken->refresh()->last_used_at)->not->toBeNull()
        ->and($buildQuery->handle($integrationToken)->pluck('key')->all())->toBe(['enabled-api-action'])
        ->and($resolveAction->handle($integrationToken, 'enabled-api-action')?->is($enabledAction))->toBeTrue()
        ->and($resolveAction->handle($integrationToken, 'disabled-api-action'))->toBeNull();

    (new RevokePublicActionIntegrationTokenAction)->handle($integrationToken);

    expect($resolveToken->handle($plainTextToken))->toBeNull();
});

it('lists active public action options alphabetically by name', function (): void {
    PublicAction::factory()->create([
        'name' => 'Zulu Action',
        'key' => 'zulu-action',
        'status' => PublicActionStatus::Active,
    ]);
    PublicAction::factory()->create([
        'name' => 'Alpha Action',
        'key' => 'alpha-action',
        'status' => PublicActionStatus::Active,
    ]);
    PublicAction::factory()->create([
        'name' => 'Paused Action',
        'key' => 'paused-action',
        'status' => PublicActionStatus::Paused,
    ]);

    expect((new ListPublicActionOptionsAction)->handle())->toBe([
        'alpha-action' => 'Alpha Action',
        'zulu-action' => 'Zulu Action',
    ]);
});

it('returns Zapier account details for the resolved integration token', function (): void {
    $integrationToken = PublicActionIntegrationToken::factory()->create([
        'name' => 'Zapier workspace',
        'provider' => PublicActionIntegrationProvider::Zapier,
    ]);
    $request = Request::create('/zapier/account');
    $request->attributes->set('public_action_integration_token', $integrationToken->load('site'));

    $response = (new ShowZapierAccountController)($request);
    $payload = $response->getData(true);

    expect($payload)->toMatchArray([
        'id' => (string) $integrationToken->getKey(),
        'name' => 'Zapier workspace',
        'provider' => PublicActionIntegrationProvider::Zapier->value,
        'site_id' => $integrationToken->site_id,
        'site_name' => $integrationToken->site?->name,
    ]);
});

it('rejects Zapier account requests without an integration token', function (): void {
    expect(fn (): mixed => (new ShowZapierAccountController)(Request::create('/zapier/account')))
        ->toThrow(HttpException::class);
});

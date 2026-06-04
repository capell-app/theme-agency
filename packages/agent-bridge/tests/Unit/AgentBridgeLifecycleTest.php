<?php

declare(strict_types=1);

use Capell\AgentBridge\Actions\CreateAgentBridgeTokenAction;
use Capell\AgentBridge\Actions\PruneAgentBridgeAuditEntriesAction;
use Capell\AgentBridge\Actions\QueryAgentBridgeAuditEntriesAction;
use Capell\AgentBridge\Actions\RevokeAgentBridgeTokenAction;
use Capell\AgentBridge\Actions\RotateAgentBridgeTokenAction;
use Capell\AgentBridge\Filament\Resources\Users\RelationManagers\AgentBridgeAuditEntriesRelationManager;
use Capell\AgentBridge\Filament\Resources\Users\RelationManagers\AgentBridgeTokensRelationManager;
use Capell\AgentBridge\Models\CapellAgentBridgeAuditEntry;
use Capell\AgentBridge\Models\CapellAgentBridgeToken;
use Capell\AgentBridge\Tests\Fixtures\User;
use Carbon\CarbonImmutable;
use Illuminate\Testing\PendingCommand;

it('creates rotates and revokes tokens through lifecycle actions', function (): void {
    $user = User::query()->create([
        'name' => 'Lifecycle User',
        'email' => 'lifecycle@example.test',
        'password' => 'secret',
    ]);

    $created = CreateAgentBridgeTokenAction::run($user, 'Lifecycle client', ['capell.pages.read'], null, '127.0.0.1');
    $token = $created['token'];
    $originalHash = $token->token_hash;

    $rotated = RotateAgentBridgeTokenAction::run($token);
    $token->refresh();

    expect($created['plainTextToken'])->toStartWith('cagent-bridge_')
        ->and($token->created_from_ip)->toBe('127.0.0.1')
        ->and($rotated['plainTextToken'])->toStartWith('cagent-bridge_')
        ->and($token->token_hash)->not->toBe($originalHash)
        ->and($token->rotated_at)->not->toBeNull()
        ->and($token->isUsable())->toBeTrue();

    RevokeAgentBridgeTokenAction::run($token);
    $token->refresh();

    expect($token->is_enabled)->toBeFalse()
        ->and($token->revoked_at)->not->toBeNull()
        ->and($token->isUsable())->toBeFalse();
});

it('exposes scope options for admin token creation', function (): void {
    expect(AgentBridgeTokensRelationManager::scopeOptions())->toHaveKeys([
        '*',
        'capell.cache.run',
        'capell.pages.read',
        'capell.pages.write',
    ]);
});

it('queries and prunes audit entries with retention boundaries', function (): void {
    $user = User::query()->create([
        'name' => 'Audit Lifecycle User',
        'email' => 'audit-lifecycle@example.test',
        'password' => 'secret',
    ]);
    $token = new CapellAgentBridgeToken([
        'name' => 'Audit lifecycle client',
        'token_hash' => CapellAgentBridgeToken::hashPlainTextToken('audit-lifecycle-token'),
        'scopes' => ['*'],
    ]);
    $token->user()->associate($user);
    $token->save();

    $oldEntry = createLifecycleAuditEntry($user, $token, 'capell.old', CarbonImmutable::now()->subDays(120));
    $newEntry = createLifecycleAuditEntry($user, $token, 'capell.new', CarbonImmutable::now()->subDay());

    $entries = QueryAgentBridgeAuditEntriesAction::run($token, null, null, 10);

    expect($entries->pluck('event')->all())->toContain('capell.old', 'capell.new')
        ->and(AgentBridgeAuditEntriesRelationManager::scopedQueryForUser(CapellAgentBridgeAuditEntry::query(), $user)->count())->toBe(2);

    expect(PruneAgentBridgeAuditEntriesAction::run(90))->toBe(1)
        ->and(CapellAgentBridgeAuditEntry::query()->whereKey($oldEntry->getKey())->exists())->toBeFalse()
        ->and(CapellAgentBridgeAuditEntry::query()->whereKey($newEntry->getKey())->exists())->toBeTrue();
});

it('registers the audit pruning command', function (): void {
    $command = $this->artisan('capell:agent-bridge-prune-audit', ['--days' => 30]);

    throw_unless($command instanceof PendingCommand, RuntimeException::class, 'Agent Bridge audit pruning command did not return a pending command.');

    $command->assertSuccessful();
});

function createLifecycleAuditEntry(User $user, CapellAgentBridgeToken $token, string $event, CarbonImmutable $createdAt): CapellAgentBridgeAuditEntry
{
    $entry = new CapellAgentBridgeAuditEntry([
        'agent_bridge_token_id' => $token->getKey(),
        'event' => $event,
        'capability_key' => 'capell.lifecycle',
        'scope' => 'capell.lifecycle',
        'payload' => ['event' => $event],
        'result' => ['ok' => true],
    ]);
    $entry->forceFill([
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);
    $entry->user()->associate($user);
    $entry->save();

    return $entry;
}

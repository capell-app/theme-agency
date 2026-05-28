<?php

declare(strict_types=1);

use Capell\AgentBridge\Actions\BuildAgentBridgePromptAction;
use Capell\AgentBridge\Actions\SaveAgentBridgePromptAction;
use Capell\AgentBridge\Data\AgentBridgePromptData;
use Capell\AgentBridge\Livewire\PromptBuilderToolbarAction;
use Capell\AgentBridge\Models\CapellAgentBridgeSavedPrompt;
use Capell\AgentBridge\Tests\Fixtures\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

it('builds an agent bridge prompt from the submitted intent', function (): void {
    $prompt = BuildAgentBridgePromptAction::run(AgentBridgePromptData::fromArray([
        'goal' => 'create a draft landing page',
        'area' => 'pages',
        'operation' => 'create',
        'safety' => 'preview_first',
        'target' => 'Spring campaign page',
        'constraints' => 'Use the default campaign layout',
        'success_criteria' => 'A draft exists but is not published',
    ]));

    expect($prompt)
        ->toContain('create a draft landing page')
        ->toContain('Area: pages')
        ->toContain('Operation: create')
        ->toContain('Safety mode: preview_first')
        ->toContain('Spring campaign page')
        ->toContain('Use the default campaign layout')
        ->toContain('A draft exists but is not published');
});

it('uses safe defaults when optional prompt context is omitted', function (): void {
    $prompt = BuildAgentBridgePromptAction::run(AgentBridgePromptData::fromArray([
        'goal' => 'inspect stale cache state',
        'area' => 'cache',
        'operation' => 'inspect',
        'safety' => 'read_only',
        'target' => '',
        'constraints' => '',
        'success_criteria' => '',
    ]));

    expect($prompt)
        ->toContain('Target/context:')
        ->toContain('Not provided.')
        ->toContain('Use Capell package boundaries, policies, and preview-first workflow.')
        ->toContain('Explain what changed or why no change is needed.');
});

it('saves loads lists and deletes prompts scoped to the authenticated admin user', function (): void {
    $user = User::query()->create([
        'name' => 'Admin One',
        'email' => 'admin-one@example.com',
        'password' => 'password',
    ]);
    $otherUser = User::query()->create([
        'name' => 'Admin Two',
        'email' => 'admin-two@example.com',
        'password' => 'password',
    ]);

    $data = AgentBridgePromptData::fromArray([
        'goal' => 'inspect page readiness',
        'area' => 'pages',
        'operation' => 'inspect',
        'safety' => 'read_only',
        'target' => 'Homepage',
    ]);

    $savedPrompt = SaveAgentBridgePromptAction::run($user, 'Readiness check', 'Homepage review', $data);
    SaveAgentBridgePromptAction::run($otherUser, 'Other admin prompt', null, $data);

    expect(CapellAgentBridgeSavedPrompt::query()->forUser($user)->pluck('name')->all())
        ->toBe(['Readiness check'])
        ->and($savedPrompt->prompt)
        ->toContain('inspect page readiness')
        ->and($savedPrompt->form_state)
        ->toMatchArray(['target' => 'Homepage']);

    $this->actingAs($user);

    Livewire::test(PromptBuilderToolbarAction::class)
        ->set('selectedSavedPromptId', $savedPrompt->id)
        ->call('loadSavedPrompt')
        ->assertSet('templateName', 'Readiness check')
        ->assertSet('data.target', 'Homepage')
        ->call('deleteSavedPrompt');

    expect(CapellAgentBridgeSavedPrompt::query()->whereKey($savedPrompt->id)->exists())->toBeFalse()
        ->and(CapellAgentBridgeSavedPrompt::query()->forUser($otherUser)->count())->toBe(1);
});

it('rejects direct saved prompt updates for another admin user', function (): void {
    $user = User::query()->create([
        'name' => 'Admin One',
        'email' => 'direct-admin-one@example.com',
        'password' => 'password',
    ]);
    $otherUser = User::query()->create([
        'name' => 'Admin Two',
        'email' => 'direct-admin-two@example.com',
        'password' => 'password',
    ]);

    $data = AgentBridgePromptData::fromArray([
        'goal' => 'inspect page readiness',
        'area' => 'pages',
        'operation' => 'inspect',
        'safety' => 'read_only',
    ]);
    $otherPrompt = SaveAgentBridgePromptAction::run($otherUser, 'Other admin prompt', null, $data);

    expect(fn (): CapellAgentBridgeSavedPrompt => SaveAgentBridgePromptAction::run(
        user: $user,
        name: 'Hijacked prompt',
        description: null,
        data: $data,
        savedPrompt: $otherPrompt,
    ))->toThrow(AuthorizationException::class);

    expect($otherPrompt->refresh()->name)->toBe('Other admin prompt');
});

it('registers the toolbar Livewire component used by the global search render hook', function (): void {
    $html = Blade::render('@livewire($component)', [
        'component' => 'capell-agent-bridge.prompt-builder-toolbar-action',
    ]);

    expect($html)
        ->toContain(__('capell-agent-bridge::admin.prompt_builder_tooltip'))
        ->toContain('wire:click="openBuilder"')
        ->not->toContain('agent-bridge-prompt-builder-title');
});

it('renders the toolbar slide-over only after click and refreshes prompt text on edits', function (): void {
    Livewire::test(PromptBuilderToolbarAction::class)
        ->assertSet('isOpen', false)
        ->assertDontSee('agent-bridge-prompt-builder-title', false)
        ->call('openBuilder')
        ->assertSet('isOpen', true)
        ->assertSee('agent-bridge-prompt-builder-title', false)
        ->set('data.goal', 'inspect updated draft state')
        ->assertSet('preparedPrompt', fn (string $prompt): bool => str_contains($prompt, 'inspect updated draft state'));
});

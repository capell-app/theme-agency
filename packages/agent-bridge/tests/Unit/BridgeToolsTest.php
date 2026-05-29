<?php

declare(strict_types=1);

use Capell\AgentBridge\Actions\InvokeAgentBridgeCapabilityPreviewAction;
use Capell\AgentBridge\Data\AuthenticatedAgentBridgeClientData;
use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Enums\CapabilityServerEnum;
use Capell\AgentBridge\Facades\CapellAgentBridge;
use Capell\AgentBridge\Models\CapellAgentBridgeToken;
use Capell\AgentBridge\Resources\CapellAgentBridgeOverviewResource;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\AgentBridge\Support\KnowledgeRepository;
use Capell\AgentBridge\Tests\Fixtures\FakeCapabilityAction;
use Capell\AgentBridge\Tests\Fixtures\User;
use Capell\AgentBridge\Tools\Boost\ListBoostCapabilitiesTool;
use Capell\AgentBridge\Tools\Boost\PreviewBoostCapabilityTool;
use Capell\AgentBridge\Tools\Knowledge\ListKnowledgePackagesTool;
use Capell\AgentBridge\Tools\Knowledge\ReadKnowledgeDocumentTool;
use Capell\AgentBridge\Tools\Site\ConfirmSiteCapabilityTool;
use Capell\AgentBridge\Tools\Site\InspectSiteStateTool;
use Capell\AgentBridge\Tools\Site\ListSiteCapabilitiesTool;
use Capell\AgentBridge\Tools\Site\RunSiteCapabilityTool;
use Laravel\Mcp\Request;

it('lists boost capabilities visible through the site server', function (): void {
    $registry = new CapellAgentBridgeCapabilityRegistry;
    $registry->register(new CapabilityData(
        key: 'capell.fake.preview',
        name: 'Fake preview',
        description: 'Preview fake capability.',
        scope: 'capell.fake.preview',
        server: CapabilityServerEnum::Site,
        risk: CapabilityRiskEnum::Read,
        actionClass: FakeCapabilityAction::class,
    ));

    $response = (new ListBoostCapabilitiesTool)->handle($registry);

    expect($response->getStructuredContent())
        ->toHaveKey('confirmation')
        ->and($response->getStructuredContent()['capabilities'][0]['key'])->toBe('capell.fake.preview');
});

it('previews a boost capability through the registry', function (): void {
    $registry = new CapellAgentBridgeCapabilityRegistry;
    $registry->register(new CapabilityData(
        key: 'capell.fake.preview',
        name: 'Fake preview',
        description: 'Preview fake capability.',
        scope: 'capell.fake.preview',
        server: CapabilityServerEnum::Site,
        risk: CapabilityRiskEnum::Read,
        actionClass: FakeCapabilityAction::class,
    ));

    $response = (new PreviewBoostCapabilityTool)->handle(
        new Request([
            'capability' => 'capell.fake.preview',
            'payload' => ['name' => 'Example'],
        ]),
        $registry,
    );

    expect($response->getStructuredContent())
        ->toHaveKey('confirmation')
        ->and($response->getStructuredContent()['mode'])->toBe('preview')
        ->and($response->getStructuredContent()['capability'])->toBe('capell.fake.preview')
        ->and($response->getStructuredContent()['preview']['message'])->toBe('Previewed fake capability.');
});

it('lists knowledge packages as structured content', function (): void {
    app()->setBasePath(getcwd() ?: dirname(__DIR__, 4));

    $response = (new ListKnowledgePackagesTool)->handle(new KnowledgeRepository);

    $packages = $response->getStructuredContent()['packages'];
    $packageNames = is_array($packages) ? array_column($packages, 'name') : [];

    expect($packageNames)
        ->toContain('capell-app/agent-bridge');
});

it('reads allowed knowledge documents by repository path', function (): void {
    app()->setBasePath(getcwd() ?: dirname(__DIR__, 4));
    config()->set('capell-agent-bridge.public_docs_paths', [
        base_path('packages/agent-bridge/docs'),
    ]);

    $response = (new ReadKnowledgeDocumentTool)->handle(
        new Request(['path' => 'packages/agent-bridge/docs/overview.md']),
        new KnowledgeRepository,
    );

    expect((string) $response->content())->toContain('Agent Bridge');
});

it('returns site state without leaking content bodies', function (): void {
    $response = (new InspectSiteStateTool)->handle();
    $structuredContent = $response->getStructuredContent();

    expect($structuredContent['app'])
        ->toHaveKeys(['name', 'environment', 'debug'])
        ->and($structuredContent['counts'])
        ->toHaveKeys(['sites', 'languages', 'pages', 'pageUrls', 'blueprints', 'redirects', 'navigations']);
});

it('lists site capabilities allowed by the authenticated client scopes', function (): void {
    $registry = new CapellAgentBridgeCapabilityRegistry;
    $registry->register(new CapabilityData(
        key: 'capell.fake.allowed',
        name: 'Fake allowed',
        description: 'Allowed fake capability.',
        scope: 'capell.fake.allowed',
        server: CapabilityServerEnum::Site,
        risk: CapabilityRiskEnum::Read,
        actionClass: FakeCapabilityAction::class,
    ));
    $registry->register(new CapabilityData(
        key: 'capell.fake.hidden',
        name: 'Fake hidden',
        description: 'Hidden fake capability.',
        scope: 'capell.fake.hidden',
        server: CapabilityServerEnum::Site,
        risk: CapabilityRiskEnum::Read,
        actionClass: FakeCapabilityAction::class,
    ));

    $response = (new ListSiteCapabilitiesTool)->handle(
        $registry,
        new AuthenticatedAgentBridgeClientData(tokenId: 1, name: 'Scoped client', scopes: ['capell.fake.allowed']),
    );

    $capabilities = $response->getStructuredContent()['capabilities'];
    $capabilityKeys = is_array($capabilities) ? array_column($capabilities, 'key') : [];

    expect($capabilityKeys)
        ->toBe(['capell.fake.allowed']);
});

it('runs and confirms site capability previews for authenticated clients', function (): void {
    $registry = resolve(CapellAgentBridgeCapabilityRegistry::class);
    $registry->register(new CapabilityData(
        key: 'capell.fake.confirmed',
        name: 'Fake confirmed',
        description: 'Confirmed fake capability.',
        scope: 'capell.fake.confirmed',
        server: CapabilityServerEnum::Site,
        risk: CapabilityRiskEnum::High,
        actionClass: FakeCapabilityAction::class,
    ));

    $user = User::query()->create([
        'name' => 'Tool User',
        'email' => 'tool-user@example.com',
        'password' => 'secret',
    ]);
    auth()->setUser($user);

    $token = new CapellAgentBridgeToken;
    $token->forceFill([
        'name' => 'Tool client',
        'token_hash' => CapellAgentBridgeToken::hashPlainTextToken('plain-token'),
        'scopes' => ['capell.fake.confirmed'],
        'user_type' => $user->getMorphClass(),
        'user_id' => $user->getKey(),
    ])->save();
    $client = new AuthenticatedAgentBridgeClientData(
        tokenId: (int) $token->getKey(),
        name: 'Tool client',
        scopes: ['capell.fake.confirmed'],
    );

    $preview = (new RunSiteCapabilityTool)->handle(
        new Request([
            'capability' => 'capell.fake.confirmed',
            'payload' => ['name' => 'Preview me'],
        ]),
        $client,
        $token,
    )->getStructuredContent();

    $confirmed = (new ConfirmSiteCapabilityTool)->handle(
        new Request([
            'confirmationToken' => $preview['confirmationToken'],
            'payload' => ['name' => 'Preview me'],
        ]),
        $client,
        $token,
    )->getStructuredContent();

    expect($preview['mode'])->toBe('preview')
        ->and($confirmed['mode'])->toBe('confirmed')
        ->and($confirmed['result']['message'])->toBe('Executed fake capability.');
});

it('directly executes read-only site capabilities without confirmation', function (): void {
    $registry = resolve(CapellAgentBridgeCapabilityRegistry::class);
    $registry->register(new CapabilityData(
        key: 'capell.fake.readonly',
        name: 'Fake readonly',
        description: 'Readonly fake capability.',
        scope: 'capell.fake.readonly',
        server: CapabilityServerEnum::Site,
        risk: CapabilityRiskEnum::Read,
        actionClass: FakeCapabilityAction::class,
        requiresConfirmation: false,
    ));

    $user = User::query()->create([
        'name' => 'Readonly Tool User',
        'email' => 'readonly-tool-user@example.com',
        'password' => 'secret',
    ]);
    auth()->setUser($user);

    $token = new CapellAgentBridgeToken;
    $token->forceFill([
        'name' => 'Readonly client',
        'token_hash' => CapellAgentBridgeToken::hashPlainTextToken('readonly-token'),
        'scopes' => ['capell.fake.readonly'],
        'user_type' => $user->getMorphClass(),
        'user_id' => $user->getKey(),
    ])->save();

    $response = (new RunSiteCapabilityTool)->handle(
        new Request([
            'capability' => 'capell.fake.readonly',
            'payload' => ['name' => 'Execute me'],
        ]),
        new AuthenticatedAgentBridgeClientData(
            tokenId: (int) $token->getKey(),
            name: 'Readonly client',
            scopes: ['capell.fake.readonly'],
        ),
        $token,
    )->getStructuredContent();

    expect($response['mode'])->toBe('executed')
        ->and($response['capability'])->toBe('capell.fake.readonly')
        ->and($response['result']['message'])->toBe('Executed fake capability.');
});

it('hashes capability payloads deterministically regardless of key order', function (): void {
    expect(InvokeAgentBridgeCapabilityPreviewAction::payloadHash([
        'second' => ['nested' => true],
        'first' => 'value',
    ]))->toBe(InvokeAgentBridgeCapabilityPreviewAction::payloadHash([
        'first' => 'value',
        'second' => ['nested' => true],
    ]));
});

it('hashes nested capability payload objects deterministically while preserving list order', function (): void {
    expect(InvokeAgentBridgeCapabilityPreviewAction::payloadHash([
        'filters' => [
            'second' => true,
            'first' => [
                'beta' => 'two',
                'alpha' => 'one',
            ],
        ],
        'steps' => [
            ['name' => 'first'],
            ['name' => 'second'],
        ],
    ]))->toBe(InvokeAgentBridgeCapabilityPreviewAction::payloadHash([
        'steps' => [
            ['name' => 'first'],
            ['name' => 'second'],
        ],
        'filters' => [
            'first' => [
                'alpha' => 'one',
                'beta' => 'two',
            ],
            'second' => true,
        ],
    ]))->not->toBe(InvokeAgentBridgeCapabilityPreviewAction::payloadHash([
        'filters' => [
            'first' => [
                'alpha' => 'one',
                'beta' => 'two',
            ],
            'second' => true,
        ],
        'steps' => [
            ['name' => 'second'],
            ['name' => 'first'],
        ],
    ]));
});

it('exposes the agent bridge overview resource as markdown text', function (): void {
    expect((string) (new CapellAgentBridgeOverviewResource)->handle()->content())
        ->toContain('Capell Agent Bridge')
        ->toContain('CapellKnowledgeServer')
        ->toContain('CapellSiteServer');
});

it('resolves the capability registry through the facade', function (): void {
    CapellAgentBridge::register(new CapabilityData(
        key: 'capell.fake.facade',
        name: 'Fake facade',
        description: 'Facade fake capability.',
        scope: 'capell.fake.facade',
        server: CapabilityServerEnum::Site,
        risk: CapabilityRiskEnum::Read,
        actionClass: FakeCapabilityAction::class,
    ));

    expect(CapellAgentBridge::has('capell.fake.facade'))->toBeTrue();
});

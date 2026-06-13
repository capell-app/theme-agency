<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Capell\LiveChat\Actions\BuildLiveChatWidgetConfigAction;
use Capell\LiveChat\Actions\GuardLiveChatInstallationOriginAction;
use Capell\LiveChat\Actions\ResolveLiveChatConversationForInstallationAction;
use Capell\LiveChat\Actions\StartLiveChatConversationAction;
use Capell\LiveChat\Data\IncomingLiveChatMessageData;
use Capell\LiveChat\Models\LiveChatConversation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('renders the public widget without exposing admin internals', function (): void {
    $this->createLiveChatSite();

    $response = $this->get(route('capell-live-chat.widget'));

    $response
        ->assertOk()
        ->assertSee('data-capell-live-chat-widget', false)
        ->assertSee(route('capell-live-chat.widget.script'), false)
        ->assertDontSee('capell-app/live-chat', false)
        ->assertDontSee('Filament', false)
        ->assertDontSee('admin/live-chat', false)
        ->assertDontSee('signed', false);
});

it('serializes browser widget config with public labels', function (): void {
    $this->createLiveChatSite();

    $config = (new BuildLiveChatWidgetConfigAction)->handle()->toArray();

    expect($config)
        ->toHaveKey('start_url')
        ->toHaveKey('message_url')
        ->toHaveKey('handoff_url')
        ->toHaveKey('labels')
        ->and($config['labels'])
        ->toMatchArray([
            'send' => 'Send',
            'request_failed' => 'Message could not be sent. Please try again.',
        ]);
});

it('injects the live chat widget through the frontend body-end hook', function (): void {
    $this->createLiveChatSite();

    $registry = app(RenderHookRegistry::class);
    $output = $registry->renderAll(RenderHookLocation::BodyEnd);

    expect($output)
        ->toContain('data-capell-live-chat-widget')
        ->toContain(str_replace('/', '\\/', route('capell-live-chat.conversations.store')))
        ->not->toContain('admin/live-chat')
        ->not->toContain('capell-app/live-chat');
});

it('does not inject the widget on ignored admin paths', function (): void {
    $this->createLiveChatSite();
    app()->instance('request', Request::create('/admin/pages', Symfony\Component\HttpFoundation\Request::METHOD_GET));

    $registry = app(RenderHookRegistry::class);
    $output = $registry->renderAll(RenderHookLocation::BodyEnd);

    expect($output)->toBe('');
});

it('declares the public conversation API as throttled and csrf-exempt', function (): void {
    $this->createLiveChatSite();
    $route = Route::getRoutes()->getByName('capell-live-chat.conversations.store');
    $externalRoute = Route::getRoutes()->getByName('capell-live-chat.api.conversations.store');

    expect($route)->not->toBeNull()
        ->and($route?->methods())->toContain('POST')
        ->and($route?->gatherMiddleware())->toContain('throttle:capell-live-chat')
        ->and($route?->excludedMiddleware())->toContain(VerifyCsrfToken::class)
        ->and($externalRoute)->not->toBeNull()
        ->and($externalRoute?->uri())->toBe('live-chat/api/{public_key}/conversations')
        ->and($externalRoute?->methods())->toContain('POST')
        ->and($externalRoute?->gatherMiddleware())->toContain('throttle:capell-live-chat')
        ->and($externalRoute?->excludedMiddleware())->toContain(VerifyCsrfToken::class);
});

it('serves an external widget script for active public keys and allowed domains', function (): void {
    $installation = $this->createLiveChatInstallation();

    $response = $this
        ->withHeader('Referer', 'https://example.test/pricing')
        ->get(route('capell-live-chat.widget.script', ['key' => $installation->public_key]));

    $response
        ->assertOk()
        ->assertHeader('Access-Control-Allow-Origin', 'https://example.test')
        ->assertSee('Example support', false)
        ->assertSee(str_replace('/', '\\/', route('capell-live-chat.api.conversations.store', ['public_key' => $installation->public_key])), false)
        ->assertDontSee('admin/live-chat', false)
        ->assertDontSee('Filament', false);
});

it('rejects external widget scripts from disallowed domains', function (): void {
    $installation = $this->createLiveChatInstallation();

    $this
        ->withHeader('Referer', 'https://blocked.test/pricing')
        ->get(route('capell-live-chat.widget.script', ['key' => $installation->public_key]))
        ->assertForbidden();
});

it('guards external installations by allowed origin', function (): void {
    $installation = $this->createLiveChatInstallation();
    $allowedRequest = Request::create('/live-chat/api/' . $installation->public_key . '/conversations', 'POST', server: [
        'HTTP_ORIGIN' => 'https://example.test',
    ]);
    $blockedRequest = Request::create('/live-chat/api/' . $installation->public_key . '/conversations', 'POST', server: [
        'HTTP_ORIGIN' => 'https://blocked.test',
    ]);

    expect(GuardLiveChatInstallationOriginAction::run($installation, $allowedRequest))->toBe('https://example.test');

    expect(fn (): string => (new GuardLiveChatInstallationOriginAction)->handle($installation, $blockedRequest))
        ->toThrow(HttpException::class);
});

it('stores installation ownership when starting external conversations', function (): void {
    $installation = $this->createLiveChatInstallation();

    $result = (new StartLiveChatConversationAction)->handle(
        new IncomingLiveChatMessageData(
            body: 'Can you help with pricing?',
            visitorToken: 'external-visitor-token',
            page: [
                'url' => 'https://example.test/pricing',
            ],
        ),
        $installation->site_id,
        $installation,
    );

    $conversation = $result['conversation'];

    expect($conversation->installation_id)->toBe($installation->getKey())
        ->and($conversation->site_id)->toBe($installation->site_id)
        ->and($conversation->visitor_token_hash)->toBe(LiveChatConversation::hashVisitorToken('external-visitor-token'));
});

it('enforces installation ownership and visitor token continuity for external messages', function (): void {
    $installation = $this->createLiveChatInstallation();
    $otherInstallation = $this->createLiveChatInstallation(allowedDomains: ['example.test']);

    $conversation = LiveChatConversation::query()->create([
        'site_id' => $installation->site_id,
        'installation_id' => $installation->getKey(),
        'visitor_token_hash' => LiveChatConversation::hashVisitorToken('external-visitor-token'),
    ]);

    expect(fn () => (new ResolveLiveChatConversationForInstallationAction)->handle($installation, $conversation->uuid, 'wrong-token'))
        ->toThrow(HttpException::class);

    expect(fn () => (new ResolveLiveChatConversationForInstallationAction)->handle($otherInstallation, $conversation->uuid, 'external-visitor-token'))
        ->toThrow(ModelNotFoundException::class);

    expect((new ResolveLiveChatConversationForInstallationAction)->handle($installation, $conversation->uuid, 'external-visitor-token')->is($conversation))
        ->toBeTrue();
});

it('returns scoped cors headers for allowed external preflight requests', function (): void {
    $installation = $this->createLiveChatInstallation();

    $this
        ->withServerVariables(['HTTP_ORIGIN' => 'https://example.test'])
        ->options(route('capell-live-chat.api.preflight', [
            'public_key' => $installation->public_key,
        ]))
        ->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', 'https://example.test')
        ->assertHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
});

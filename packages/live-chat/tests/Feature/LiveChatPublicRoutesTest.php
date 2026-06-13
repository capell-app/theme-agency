<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Capell\LiveChat\Actions\BuildLiveChatWidgetConfigAction;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

    $config = BuildLiveChatWidgetConfigAction::run()->toArray();

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

    /** @var RenderHookRegistry $registry */
    $registry = resolve(RenderHookRegistry::class);
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

    /** @var RenderHookRegistry $registry */
    $registry = resolve(RenderHookRegistry::class);
    $output = $registry->renderAll(RenderHookLocation::BodyEnd);

    expect($output)->toBe('');
});

it('declares the public conversation API as throttled and csrf-exempt', function (): void {
    $this->createLiveChatSite();
    $route = Route::getRoutes()->getByName('capell-live-chat.conversations.store');

    expect($route)->not->toBeNull()
        ->and($route?->methods())->toContain('POST')
        ->and($route?->gatherMiddleware())->toContain('throttle:capell-live-chat')
        ->and($route?->excludedMiddleware())->toContain(VerifyCsrfToken::class);
});

<?php

declare(strict_types=1);

use Capell\AgentBridge\Http\Middleware\AuthenticateCapellAgentBridgeToken;
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

it('does not expose agent-bridge discovery or knowledge routes by default', function (): void {
    $this->get('/')
        ->assertNotFound();

    $this->get('/agent-bridge/capell/knowledge')
        ->assertNotFound();
});

it('does not fall back to the host homepage when route config is missing', function (): void {
    config()->set('capell-agent-bridge.routes', [
        'site' => 'agent-bridge/capell',
    ]);

    require __DIR__ . '/../../routes/agent-bridge.php';

    $this->get('/')
        ->assertNotFound();
});

it('can return agent-bridge discovery details from a configured home route', function (): void {
    config()->set('capell-agent-bridge.routes.home', 'agent-bridge/capell/discover');
    config()->set('capell-agent-bridge.routes.knowledge', 'agent-bridge/capell/knowledge');
    config()->set('capell-agent-bridge.routes.site', 'agent-bridge/capell');

    require __DIR__ . '/../../routes/agent-bridge.php';

    $this->get('/agent-bridge/capell/discover')
        ->assertOk()
        ->assertJson([
            'name' => 'Capell Agent Bridge',
            'status' => 'ok',
            'servers' => [
                'knowledge' => 'http://localhost/agent-bridge/capell/knowledge',
                'site' => 'http://localhost/agent-bridge/capell',
            ],
        ]);
});

it('protects the configured knowledge server route with bearer token middleware', function (): void {
    config()->set('capell-agent-bridge.routes.home');
    config()->set('capell-agent-bridge.routes.knowledge', 'agent-bridge/capell/knowledge');
    config()->set('capell-agent-bridge.routes.site');

    require __DIR__ . '/../../routes/agent-bridge.php';

    if (! class_exists(Mcp::class)) {
        expect(true)->toBeTrue();

        return;
    }

    $knowledgeRoute = null;

    foreach (Route::getRoutes()->getRoutes() as $route) {
        if (! $route instanceof IlluminateRoute) {
            continue;
        }

        if (str_contains($route->uri(), 'agent-bridge/capell/knowledge') && in_array('POST', $route->methods(), true)) {
            $knowledgeRoute = $route;

            break;
        }
    }

    expect($knowledgeRoute)->toBeInstanceOf(IlluminateRoute::class);
    assert($knowledgeRoute instanceof IlluminateRoute);

    expect($knowledgeRoute)->not->toBeNull()
        ->and($knowledgeRoute->gatherMiddleware())->toContain(AuthenticateCapellAgentBridgeToken::class);
});

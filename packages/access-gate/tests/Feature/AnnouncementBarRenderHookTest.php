<?php

declare(strict_types=1);

use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Providers\AccessGateServiceProvider;
use Capell\AccessGate\Support\RenderHooks\RegisterAnnouncementBarHook;
use Capell\Core\Facades\CapellCore;
use Capell\Frontend\Data\RenderHookContext;
use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Support\Render\FrontendHookRegistrar;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Illuminate\Http\Request;

it('renders anonymous safe announcement output through the public hook', function (): void {
    app()->instance('request', Request::create('/extensions/themes'));

    Area::factory()->create([
        'key' => 'capell-preview',
        'announcement_enabled' => true,
        'announcement_message' => 'Launch <script>alert("x")</script> offer.',
        'announcement_link_label' => 'Claim <strong>offer</strong>',
        'announcement_link_url' => '/marketplace/browse',
        'announcement_path_patterns' => [
            'extensions/*',
        ],
    ]);

    $html = (new RegisterAnnouncementBarHook)->render(new RenderHookContext(RenderHookLocation::BodyStart->value, null));

    expect($html)
        ->toContain('site-announcement')
        ->toContain('role="status"')
        ->toContain('Launch &lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt; offer.')
        ->toContain('href="/marketplace/browse"')
        ->toContain('Claim &lt;strong&gt;offer&lt;/strong&gt;')
        ->not->toContain('<script>')
        ->not->toContain('<strong>')
        ->not->toContain('capell-access-gate')
        ->not->toContain('access_gate')
        ->not->toContain('filament')
        ->not->toContain('admin')
        ->not->toContain('model');
});

it('contributes the announcement bar to the body start render hook', function (): void {
    CapellCore::forcePackageInstalled(AccessGateServiceProvider::$packageName);

    $registry = new RenderHookRegistry;
    app()->instance(RenderHookRegistry::class, $registry);
    app()->instance(FrontendHookRegistrar::class, new FrontendHookRegistrar($registry));

    $provider = new AccessGateServiceProvider(app());
    $method = new ReflectionMethod(AccessGateServiceProvider::class, 'registerFrontendRenderHooks');
    $method->invoke($provider);

    $contributions = $registry->contributions();

    expect($contributions)->toHaveKey(RenderHookLocation::BodyStart->value)
        ->and($contributions[RenderHookLocation::BodyStart->value][0]['owner'])->toBe(AccessGateServiceProvider::$packageName)
        ->and($contributions[RenderHookLocation::BodyStart->value][0]['key'])->toBe('announcement-bar')
        ->and($contributions[RenderHookLocation::BodyStart->value][0]['cacheSafe'])->toBeTrue();
});

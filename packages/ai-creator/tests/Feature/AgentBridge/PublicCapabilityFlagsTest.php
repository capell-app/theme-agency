<?php

declare(strict_types=1);

use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\Core\Support\CapellCoreManager;

it('flags exactly the 7 anonymous-read capabilities public and nothing else', function (): void {
    // publiclyReadable() filters by the registry's CapellCoreManager singleton.
    // The TestCase forces install via the facade (a different early instance), so
    // mark the package installed on the resolved singleton the registry consults.
    app(CapellCoreManager::class)->forcePackageInstalled('capell-app/ai-creator');

    $registry = app(CapellAgentBridgeCapabilityRegistry::class);
    $publicKeys = $registry->publiclyReadable()->map->key->all();

    expect($publicKeys)->toEqualCanonicalizing([
        'capell.ai-creator.discovery.list_themes',
        'capell.ai-creator.discovery.list_page_types',
        'capell.ai-creator.discovery.list_section_types',
        'capell.ai-creator.discovery.list_layouts',
        'capell.ai-creator.interview.get',
        'capell.ai-creator.discovery.get_site_spec_schema',
        'capell.ai-creator.discovery.validate_spec',
    ]);

    // Mutating / session-scoped caps must NOT be public.
    expect($publicKeys)->not->toContain('capell.ai-creator.build_preview')
        ->and($publicKeys)->not->toContain('capell.ai-creator.export_site')
        ->and($publicKeys)->not->toContain('capell.ai-creator.sessions.preview_apply');
});

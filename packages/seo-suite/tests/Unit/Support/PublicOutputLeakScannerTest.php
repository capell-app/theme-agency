<?php

declare(strict_types=1);

use Capell\SeoSuite\Support\PublicOutputLeakScanner;

it('detects public output markers that expose editor and model state', function (): void {
    $labels = (new PublicOutputLeakScanner)->labels(implode("\n", [
        '<a href="/admin/pages/123?signature=abc&expires=123">Edit</a>',
        '<div wire:click="save" data-field-path="content.blocks.0.title" data-model-id="123">',
        '<div wire:id="abcdef" wire:snapshot="{&quot;memo&quot;:{}}"></div>',
        '<script>window.CapellFrontendAuthoring = window.CapellFrontendAuthoring || {};</script>',
        '<iframe src="/authoring/regions/payload?expires=123&signature=abc"></iframe>',
        '{"pageId": 456, "isDraft": true, "permissions": ["update"]}',
        '{"editable_regions":[{"edit_url":"/authoring/regions/payload","selector":"#main h1","recordKey":123,"pageUrlId":456,"siteId":1,"languageId":1}]}',
        '<span data-capell-authoring-save-toolbar data-editable="title">Draft copy</span>',
    ]));

    expect($labels)->toContain(
        __('capell-seo-suite::generic.public_output_leak_admin_url'),
        __('capell-seo-suite::generic.public_output_leak_signature_parameter'),
        __('capell-seo-suite::generic.public_output_leak_expires_parameter'),
        __('capell-seo-suite::generic.public_output_leak_livewire_directive'),
        __('capell-seo-suite::generic.public_output_leak_field_path'),
        __('capell-seo-suite::generic.public_output_leak_model_id'),
        __('capell-seo-suite::generic.public_output_leak_page_id'),
        __('capell-seo-suite::generic.public_output_leak_editor_metadata'),
        __('capell-seo-suite::generic.public_output_leak_permission_marker'),
        __('capell-seo-suite::generic.public_output_leak_draft_marker'),
    );
});

it('does not flag ordinary public content that uses similar words', function (): void {
    $labels = (new PublicOutputLeakScanner)->labels(implode("\n", [
        '# Contact the administrator',
        'This public article mentions Livewire, model IDs, page IDs, draft copy, and permissions in prose.',
        '<a href="/administer-your-account">Administer your account</a>',
        '<a href="/filamentous-algae">Filamentous algae</a>',
        '<a href="/draft-beer">Draft beer guide</a>',
        '<a href="/products?expires_in=30">Offer expires in thirty days</a>',
        '{"modelName":"Example","pageTitle":"Public page","fieldLabel":"Name"}',
    ]));

    expect($labels)->toBe([]);
});

it('redacts signed diagnostic values without hiding the unsafe path', function (): void {
    $redacted = (new PublicOutputLeakScanner)->redact(
        'https://example.test/admin/pages/123?signature=secret&expires=999&token=private',
    );

    expect($redacted)->toBe('https://example.test/admin/pages/123?signature=[redacted]&expires=[redacted]&token=[redacted]')
        ->and($redacted)->not->toContain('secret', '999', 'private');
});

it('redacts signed authoring route payloads', function (): void {
    $redacted = (new PublicOutputLeakScanner)->redact(
        'https://example.test/authoring/regions/eyJkYXRhIjp7fQ?expires=999&signature=secret',
    );

    expect($redacted)->toBe('https://example.test/authoring/regions/[redacted]?expires=[redacted]&signature=[redacted]')
        ->and($redacted)->not->toContain('eyJkYXRhIjp7fQ', '999', 'secret');
});

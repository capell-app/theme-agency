<?php

declare(strict_types=1);

use Capell\SeoSuite\Support\PublicOutputLeakScanner;

it('detects public output markers that expose editor and model state', function (): void {
    $labels = (new PublicOutputLeakScanner)->labels(implode("\n", [
        '<a href="/admin/pages/123?signature=abc&expires=123">Edit</a>',
        '<div wire:click="save" data-field-path="content.blocks.0.title" data-model-id="123">',
        '{"pageId": 456, "is_draft": true, "permissions": ["update"]}',
        '<span data-editable="title">Draft copy</span>',
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

it('redacts signed diagnostic values without hiding the unsafe path', function (): void {
    $redacted = (new PublicOutputLeakScanner)->redact(
        'https://example.test/admin/pages/123?signature=secret&expires=999&token=private',
    );

    expect($redacted)->toBe('https://example.test/admin/pages/123?signature=[redacted]&expires=[redacted]&token=[redacted]')
        ->and($redacted)->not->toContain('secret', '999', 'private');
});

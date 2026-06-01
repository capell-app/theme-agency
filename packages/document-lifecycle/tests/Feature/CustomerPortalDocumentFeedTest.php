<?php

declare(strict_types=1);

use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Language;
use Capell\Core\Models\Theme;
use Capell\CustomerPortal\Actions\ResolvePortalSelfServiceItemsAction;
use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Enums\PortalSelfServiceItemType;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Illuminate\Support\Facades\DB;

it('registers accepted documents as portal self service items', function (): void {
    $portalAccount = PortalAccount::query()->create([
        'site_id' => createCustomerPortalDocumentSite(),
        'email' => 'reader@example.com',
        'display_name' => 'Reader Example',
    ]);
    $document = Document::query()->create([
        'key' => 'terms',
        'title' => 'Terms of Service',
        'status' => DocumentStatusEnum::Active,
    ]);
    $publication = DocumentPublication::query()->create([
        'document_id' => $document->getKey(),
        'version_label' => 'v2',
        'content_hash' => hash('sha256', 'terms-v2'),
        'published_at' => now(),
    ]);
    $acceptance = DocumentAcceptance::query()->create([
        'acceptor_type' => 'portal_account',
        'acceptor_id' => $portalAccount->getKey(),
        'document_key' => 'terms',
        'document_version' => 'v2',
        'document_publication_id' => $publication->getKey(),
        'document_hash' => $publication->content_hash,
        'accepted_at' => now(),
        'context' => 'customer_portal',
    ]);
    DocumentAcceptance::query()->create([
        'acceptor_type' => 'portal_account',
        'acceptor_id' => $portalAccount->getKey() + 1,
        'document_key' => 'privacy',
        'document_version' => 'v1',
        'accepted_at' => now(),
    ]);

    $items = ResolvePortalSelfServiceItemsAction::run($portalAccount);
    $item = collect($items)->first();

    expect($items)->toHaveCount(1)
        ->and($item)->toBeInstanceOf(PortalSelfServiceItemData::class);

    throw_unless($item instanceof PortalSelfServiceItemData, RuntimeException::class, 'Expected portal item.');

    expect($item->key)->toBe('document-lifecycle.acceptance.' . $acceptance->getKey())
        ->and($item->type)->toBe(PortalSelfServiceItemType::Document)
        ->and($item->label)->toBe('Terms of Service')
        ->and($item->status)->toBe(__('capell-document-lifecycle::navigation.portal.accepted_status'))
        ->and($item->meta)->toBe([
            'acceptance_id' => (int) $acceptance->getKey(),
            'document_key' => 'terms',
            'document_version' => 'v2',
            'document_publication_id' => (int) $publication->getKey(),
        ]);
});

it('declares customer portal document feed metadata in the document lifecycle manifest', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['surfaces'])->toContain('frontend')
        ->and($manifest['dependencies']['supports'] ?? [])->toContain('capell-app/customer-portal')
        ->and($manifest['capabilities'])->toContain('document-lifecycle-customer-portal-document-feed');
});

function createCustomerPortalDocumentSite(): int
{
    return (int) DB::table('sites')->insertGetId([
        'name' => 'Document Portal Site',
        'blueprint_id' => Blueprint::factory()->site()->create()->getKey(),
        'theme_id' => Theme::factory()->create([
            'blueprint_id' => Blueprint::factory()->theme()->create()->getKey(),
        ])->getKey(),
        'language_id' => Language::factory()->english()->create()->getKey(),
        'default' => true,
        'status' => true,
    ]);
}

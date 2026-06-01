<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Support\CustomerPortal;

use Capell\CustomerPortal\Contracts\PortalSelfServiceItemProvider;
use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Enums\PortalSelfServiceItemType;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Illuminate\Database\Eloquent\Builder;

final class DocumentLifecyclePortalSelfServiceItemProvider implements PortalSelfServiceItemProvider
{
    /**
     * @return iterable<PortalSelfServiceItemData>
     */
    public function selfServiceItemsFor(PortalAccount $portalAccount): iterable
    {
        return DocumentAcceptance::query()
            ->with(['publication.document'])
            ->where(function (Builder $query) use ($portalAccount): void {
                $query
                    ->where(function (Builder $query) use ($portalAccount): void {
                        $query
                            ->whereIn('acceptor_type', $this->portalAccountMorphTypes())
                            ->where('acceptor_id', $portalAccount->getKey());
                    })
                    ->orWhere(function (Builder $query) use ($portalAccount): void {
                        $query
                            ->whereIn('subject_type', $this->portalAccountMorphTypes())
                            ->where('subject_id', $portalAccount->getKey());
                    });
            })
            ->latest('accepted_at')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (DocumentAcceptance $acceptance): PortalSelfServiceItemData => new PortalSelfServiceItemData(
                key: 'document-lifecycle.acceptance.' . $acceptance->getKey(),
                type: PortalSelfServiceItemType::Document,
                label: $acceptance->publication?->document?->title ?? $acceptance->document_key,
                description: __('capell-document-lifecycle::navigation.portal.acceptance_description', [
                    'version' => $acceptance->document_version,
                ]),
                status: __('capell-document-lifecycle::navigation.portal.accepted_status'),
                occurredAt: $acceptance->accepted_at,
                meta: [
                    'acceptance_id' => (int) $acceptance->getKey(),
                    'document_key' => $acceptance->document_key,
                    'document_version' => $acceptance->document_version,
                    'document_publication_id' => $acceptance->document_publication_id,
                ],
            ))
            ->all();
    }

    /**
     * @return list<string>
     */
    private function portalAccountMorphTypes(): array
    {
        return [
            'portal_account',
            PortalAccount::class,
        ];
    }
}

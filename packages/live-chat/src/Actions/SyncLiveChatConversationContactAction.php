<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\Contacts\Actions\SyncContactSourceRecordAction;
use Capell\Contacts\Data\ContactSourceRecordData;
use Capell\Contacts\Data\ContactSourceSyncResultData;
use Capell\Contacts\Enums\ContactActivityType;
use Capell\Contacts\Enums\LeadStatus;
use Capell\LiveChat\Models\LiveChatConversation;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncLiveChatConversationContactAction
{
    use AsAction;

    public function handle(LiveChatConversation $conversation, ?string $leadTitle = null): ?ContactSourceSyncResultData
    {
        if (! $this->hasContactDetails($conversation)) {
            return null;
        }

        $transcript = (new BuildLiveChatTranscriptAction)->handle($conversation);

        $resolvedLeadTitle = $conversation->lead_id === null
            ? ($leadTitle ?? $this->leadTitle($conversation))
            : null;

        $result = (new SyncContactSourceRecordAction)->handle(
            new ContactSourceRecordData(
                siteId: (int) $conversation->site_id,
                sourceKey: 'live-chat',
                sourceIdentifier: $conversation->uuid,
                email: $conversation->visitor_email,
                phone: $conversation->visitor_phone,
                displayName: $conversation->visitor_name,
                profile: [
                    'company' => $conversation->visitor_company,
                    'preferred_callback_at' => $conversation->preferred_callback_at?->toISOString(),
                    'live_chat' => [
                        'conversation_uuid' => $conversation->uuid,
                        'flow' => $conversation->flow->value,
                        'intent' => $conversation->intent?->value,
                    ],
                ],
                tags: ['live-chat'],
                activityType: ContactActivityType::Note,
                activitySummary: __('capell-live-chat::generic.contact_sync.activity_summary'),
                activityPayload: [
                    'conversation_uuid' => $conversation->uuid,
                    'status' => $conversation->status->value,
                    'flow' => $conversation->flow->value,
                    'intent' => $conversation->intent?->value,
                    'priority' => $conversation->priority->value,
                    'handoff_requested_at' => $conversation->handoff_requested_at?->toISOString(),
                    'transcript' => $transcript,
                ],
                leadTitle: $resolvedLeadTitle,
                leadStatus: LeadStatus::New,
                occurredAt: CarbonImmutable::now(),
            ),
            $conversation,
        );

        $conversation->forceFill([
            'contact_id' => $result->contact->getKey(),
            'lead_id' => $conversation->lead_id ?? $result->lead?->getKey(),
            'contact_captured_at' => $conversation->contact_captured_at ?? CarbonImmutable::now(),
        ])->save();

        return $result;
    }

    private function hasContactDetails(LiveChatConversation $conversation): bool
    {
        return $this->filled($conversation->visitor_email)
            || $this->filled($conversation->visitor_phone)
            || $this->filled($conversation->visitor_name);
    }

    private function filled(?string $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    private function leadTitle(LiveChatConversation $conversation): ?string
    {
        if ($conversation->handoff_requested_at !== null || $conversation->escalated_at !== null) {
            return __('capell-live-chat::generic.contact_sync.follow_up_lead_title', [
                'visitor' => $conversation->visitor_name ?: $conversation->uuid,
            ]);
        }

        if ($conversation->intent?->value === 'sales') {
            return __('capell-live-chat::generic.contact_sync.sales_lead_title', [
                'visitor' => $conversation->visitor_name ?: $conversation->uuid,
            ]);
        }

        return null;
    }
}

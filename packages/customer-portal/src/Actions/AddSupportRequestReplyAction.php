<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Actions;

use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Capell\CustomerPortal\Models\PortalSupportRequestReply;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PortalSupportRequestReply run(PortalSupportRequest $supportRequest, string $message, string $senderType, ?Model $author = null, array<int, array<string, mixed>> $attachments = [])
 */
final class AddSupportRequestReplyAction
{
    use AsAction;

    /**
     * @param  array<int, array<string, mixed>>  $attachments
     */
    public function handle(
        PortalSupportRequest $supportRequest,
        string $message,
        string $senderType,
        ?Model $author = null,
        array $attachments = [],
    ): PortalSupportRequestReply {
        $message = trim($message);

        if ($message === '') {
            throw ValidationException::withMessages([
                'message' => __('capell-customer-portal::validation.support_message_required'),
            ]);
        }

        return DB::transaction(function () use ($supportRequest, $message, $senderType, $author, $attachments): PortalSupportRequestReply {
            /** @var PortalSupportRequestReply $reply */
            $reply = $supportRequest->replies()->create([
                'sender_type' => $senderType,
                'author_type' => $author?->getMorphClass(),
                'author_id' => $author?->getKey(),
                'message' => $message,
                'attachments' => $attachments === [] ? null : $attachments,
                'submitted_at' => now(),
            ]);

            $nextStatus = $senderType === 'customer'
                ? SupportRequestStatus::WaitingOnTeam
                : SupportRequestStatus::WaitingOnCustomer;

            UpdateSupportRequestStatusAction::run($supportRequest, $nextStatus);

            return $reply;
        });
    }
}

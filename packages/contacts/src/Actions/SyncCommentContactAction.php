<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Actions\Concerns\CoercesContactSourceValues;
use Capell\Contacts\Data\ContactSourceRecordData;
use Capell\Contacts\Data\ContactSourceSyncResultData;
use Capell\Contacts\Enums\ContactActivityType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncCommentContactAction
{
    use AsAction;
    use CoercesContactSourceValues;

    public function handle(object $event): ?ContactSourceSyncResultData
    {
        $comment = $event->comment ?? null;

        if (! $comment instanceof Model) {
            return null;
        }

        $commentId = $this->intValue($comment->getKey());
        $siteId = $this->intValue($comment->getAttribute('site_id'));
        $author = $this->relatedModel($comment, 'author');

        if ($commentId === null || $siteId === null || ! $author instanceof Model) {
            return null;
        }

        $submittedAt = $comment->getAttribute('submitted_at');

        return SyncContactSourceRecordAction::run(
            new ContactSourceRecordData(
                siteId: $siteId,
                sourceKey: 'comments',
                sourceIdentifier: 'comment-' . $commentId,
                email: $this->stringValue($author->getAttribute('email')),
                displayName: $this->stringValue($author->getAttribute('name')),
                profile: [
                    'comments' => [
                        'author_id' => $this->intValue($author->getKey()),
                        'comment_id' => $commentId,
                        'commentable_type' => $this->stringValue($comment->getAttribute('commentable_type')),
                        'commentable_id' => $this->intValue($comment->getAttribute('commentable_id')),
                        'status' => $this->stringValue($comment->getAttribute('status')?->value ?? $comment->getAttribute('status')),
                    ],
                ],
                tags: ['comments'],
                activityType: ContactActivityType::Comment,
                activitySummary: __('capell-contacts::generic.comments.activity_summary'),
                activityPayload: [
                    'author_id' => $this->intValue($author->getKey()),
                    'comment_id' => $commentId,
                    'commentable_type' => $this->stringValue($comment->getAttribute('commentable_type')),
                    'commentable_id' => $this->intValue($comment->getAttribute('commentable_id')),
                    'status' => $this->stringValue($comment->getAttribute('status')?->value ?? $comment->getAttribute('status')),
                    'public_id' => $this->stringValue($comment->getAttribute('public_id')),
                ],
                occurredAt: $submittedAt instanceof CarbonInterface ? $submittedAt : null,
            ),
            $comment,
        );
    }
}

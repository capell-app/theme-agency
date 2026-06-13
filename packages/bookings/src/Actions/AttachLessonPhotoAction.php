<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\LessonNote;
use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static LessonNote run(LessonNote $lessonNote, int $mediaId, ?PortalAccount $portalAccount = null)
 */
class AttachLessonPhotoAction
{
    use AsAction;

    public function handle(LessonNote $lessonNote, int $mediaId, ?PortalAccount $portalAccount = null): LessonNote
    {
        if ($portalAccount instanceof PortalAccount) {
            $this->assertPortalAccountOwnsLessonNote($lessonNote, $portalAccount);
        }

        $photoMediaIds = array_values(array_unique([
            ...array_map(static fn (mixed $value): int => (int) $value, $lessonNote->photo_media_ids ?? []),
            $mediaId,
        ]));

        $lessonNote->forceFill([
            'photo_media_ids' => $photoMediaIds,
            'metadata_stripped_at' => CarbonImmutable::now(),
        ])->save();

        return $lessonNote->refresh();
    }

    private function assertPortalAccountOwnsLessonNote(LessonNote $lessonNote, PortalAccount $portalAccount): void
    {
        if ($lessonNote->site_id !== $portalAccount->site_id || $lessonNote->portal_account_id !== $portalAccount->getKey()) {
            throw ValidationException::withMessages([
                'portal_account_id' => __('capell-bookings::validation.portal_lesson_scope_mismatch'),
            ]);
        }
    }
}

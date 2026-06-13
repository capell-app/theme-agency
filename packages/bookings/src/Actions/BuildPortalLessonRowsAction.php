<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Data\PortalLessonRowData;
use Capell\Bookings\Enums\LessonNoteVisibilityEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\LessonNote;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Collection<int, PortalLessonRowData> run(int $siteId, PortalAccount $portalAccount)
 */
class BuildPortalLessonRowsAction
{
    use AsAction;

    /**
     * @return Collection<int, PortalLessonRowData>
     */
    public function handle(int $siteId, PortalAccount $portalAccount): Collection
    {
        if ($portalAccount->site_id !== $siteId) {
            throw ValidationException::withMessages([
                'site_id' => __('capell-bookings::validation.portal_account_site_mismatch'),
            ]);
        }

        /** @var EloquentCollection<int, AppointmentRequest> $appointmentRequests */
        $appointmentRequests = AppointmentRequest::query()
            ->with('service')
            ->where('site_id', $siteId)
            ->where('portal_account_id', $portalAccount->getKey())
            ->orderByDesc('requested_starts_at')
            ->get();

        /** @var Collection<int, EloquentCollection<int, LessonNote>> $sharedNotesByAppointment */
        $sharedNotesByAppointment = LessonNote::query()
            ->whereIn('appointment_request_id', $appointmentRequests->pluck('id')->all())
            ->where('visibility', LessonNoteVisibilityEnum::Shared->value)
            ->orderBy('occurred_at')
            ->get()
            ->groupBy('appointment_request_id');

        return $appointmentRequests->map(static function (AppointmentRequest $appointmentRequest) use ($sharedNotesByAppointment): PortalLessonRowData {
            $sharedNotes = $sharedNotesByAppointment->get((int) $appointmentRequest->id, new EloquentCollection);

            return new PortalLessonRowData(
                appointmentRequestId: (int) $appointmentRequest->id,
                serviceName: $appointmentRequest->service->name,
                startsAt: $appointmentRequest->requested_starts_at,
                endsAt: $appointmentRequest->requested_ends_at,
                status: $appointmentRequest->status,
                sharedNotes: array_values($sharedNotes
                    ->map(static fn (LessonNote $lessonNote): array => [
                        'id' => (int) $lessonNote->id,
                        'summary' => $lessonNote->summary,
                        'body' => $lessonNote->body,
                        'photo_media_ids' => array_map(static fn (mixed $value): int => (int) $value, $lessonNote->photo_media_ids ?? []),
                    ])
                    ->values()
                    ->all()),
            );
        });
    }
}

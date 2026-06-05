<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Actions;

use Capell\PrivacyCenter\Data\PrivacyRequestData;
use Capell\PrivacyCenter\Enums\PrivacyRequestStatus;
use Capell\PrivacyCenter\Models\PrivacyRequest;
use Capell\PrivacyCenter\Support\PrivacyIdentifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PrivacyRequest run(PrivacyRequestData $requestData, ?Model $requester = null, ?Model $subject = null)
 */
final class OpenPrivacyRequestAction
{
    use AsAction;

    public function handle(
        PrivacyRequestData $requestData,
        ?Model $requester = null,
        ?Model $subject = null,
    ): PrivacyRequest {
        $submittedAt = $requestData->submittedAt ?? now();
        $dueAt = $requestData->dueAt ?? $submittedAt->copy()->addDays($this->defaultDueDays());

        return PrivacyRequest::query()->create([
            'site_id' => $requestData->siteId,
            'requester_type' => PrivacyIdentifier::morphType($requester),
            'requester_id' => $requester?->getKey(),
            'subject_type' => PrivacyIdentifier::morphType($subject),
            'subject_id' => $subject?->getKey(),
            'reference' => $this->uniqueReference(),
            'type' => $requestData->type,
            'status' => PrivacyRequestStatus::Submitted,
            'email_hash' => PrivacyIdentifier::hashNullable($requestData->email),
            'submitted_at' => $submittedAt,
            'due_at' => $dueAt,
            'workflow_payload' => $requestData->workflowPayload === [] ? null : $requestData->workflowPayload,
            'metadata' => $requestData->metadata === [] ? null : $requestData->metadata,
        ]);
    }

    private function uniqueReference(): string
    {
        do {
            $reference = 'PR-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));
        } while (PrivacyRequest::query()->where('reference', $reference)->exists());

        return $reference;
    }

    private function defaultDueDays(): int
    {
        $dueDays = config('capell-privacy-center.privacy_request_due_days', 30);

        return is_int($dueDays) && $dueDays > 0 ? $dueDays : 30;
    }
}

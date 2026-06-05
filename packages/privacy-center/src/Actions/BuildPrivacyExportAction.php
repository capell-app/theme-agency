<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Actions;

use Capell\PrivacyCenter\Data\PrivacyExportData;
use Capell\PrivacyCenter\Models\ConsentRecord;
use Capell\PrivacyCenter\Models\PolicyAcceptance;
use Capell\PrivacyCenter\Models\PrivacyRequest;
use Capell\PrivacyCenter\Support\PrivacyIdentifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PrivacyExportData run(Model $subject)
 */
final class BuildPrivacyExportAction
{
    use AsAction;

    public function handle(Model $subject): PrivacyExportData
    {
        $subjectType = PrivacyIdentifier::morphType($subject) ?? $subject::class;
        $subjectId = $subject->getKey();

        return new PrivacyExportData(
            subjectType: $subjectType,
            subjectId: $subjectId,
            consentRecords: $this->exportRows(ConsentRecord::query()
                ->where('subject_type', $subjectType)
                ->where('subject_id', $subjectId)
                ->oldest('decided_at')
                ->get()
                ->map(static fn (ConsentRecord $record): array => Arr::except($record->toArray(), [
                    'id',
                    'subject_type',
                    'subject_id',
                    'source_type',
                    'source_id',
                    'ip_hash',
                    'user_agent_hash',
                ]))
                ->all()),
            policyAcceptances: $this->exportRows(PolicyAcceptance::query()
                ->where('subject_type', $subjectType)
                ->where('subject_id', $subjectId)
                ->oldest('accepted_at')
                ->get()
                ->map(static fn (PolicyAcceptance $acceptance): array => Arr::except($acceptance->toArray(), [
                    'id',
                    'subject_type',
                    'subject_id',
                    'ip_hash',
                    'user_agent_hash',
                ]))
                ->all()),
            privacyRequests: $this->exportRows(PrivacyRequest::query()
                ->where('subject_type', $subjectType)
                ->where('subject_id', $subjectId)
                ->oldest('submitted_at')
                ->get()
                ->map(static fn (PrivacyRequest $privacyRequest): array => Arr::except($privacyRequest->toArray(), [
                    'id',
                    'requester_type',
                    'requester_id',
                    'subject_type',
                    'subject_id',
                    'email_hash',
                ]))
                ->all()),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function exportRows(array $rows): array
    {
        return array_values($rows);
    }
}

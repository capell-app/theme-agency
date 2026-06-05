<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Actions;

use Capell\PrivacyCenter\Models\ConsentRecord;
use Capell\PrivacyCenter\Models\PolicyAcceptance;
use Capell\PrivacyCenter\Models\PrivacyRequest;
use Capell\PrivacyCenter\Support\PrivacyIdentifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(Model $subject)
 */
final class AnonymizePrivacySubjectAction
{
    use AsAction;

    public function handle(Model $subject): int
    {
        $subjectType = PrivacyIdentifier::morphType($subject) ?? $subject::class;
        $subjectId = $subject->getKey();

        return DB::transaction(function () use ($subjectId, $subjectType): int {
            $affectedRecords = 0;

            $affectedRecords += ConsentRecord::query()
                ->where('subject_type', $subjectType)
                ->where('subject_id', $subjectId)
                ->update([
                    'subject_type' => null,
                    'subject_id' => null,
                    'source_type' => null,
                    'source_id' => null,
                    'ip_hash' => null,
                    'user_agent_hash' => null,
                    'evidence' => null,
                    'metadata' => null,
                ]);

            $affectedRecords += PolicyAcceptance::query()
                ->where('subject_type', $subjectType)
                ->where('subject_id', $subjectId)
                ->update([
                    'subject_type' => null,
                    'subject_id' => null,
                    'ip_hash' => null,
                    'user_agent_hash' => null,
                    'metadata' => null,
                ]);

            return $affectedRecords + PrivacyRequest::query()
                ->where('subject_type', $subjectType)
                ->where('subject_id', $subjectId)
                ->update([
                    'requester_type' => null,
                    'requester_id' => null,
                    'subject_type' => null,
                    'subject_id' => null,
                    'email_hash' => null,
                    'workflow_payload' => null,
                    'metadata' => null,
                ]);
        });
    }
}

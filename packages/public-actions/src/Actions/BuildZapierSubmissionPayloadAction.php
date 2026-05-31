<?php

declare(strict_types=1);

namespace Capell\PublicActions\Actions;

use Capell\PublicActions\Data\PublicActionZapierSubmissionData;
use Capell\PublicActions\Models\PublicActionSubmission;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildZapierSubmissionPayloadAction
{
    use AsAction;

    public function handle(PublicActionSubmission $submission): PublicActionZapierSubmissionData
    {
        $site = $submission->site;

        return new PublicActionZapierSubmissionData(
            id: (string) $submission->getKey(),
            actionKey: (string) $submission->action?->key,
            submittedAt: $submission->submitted_at?->toIso8601String() ?? $submission->created_at?->toIso8601String() ?? now()->toIso8601String(),
            payload: $submission->payload ?? [],
            siteName: is_string($site?->name) ? $site->name : null,
            sourceType: $submission->source_type,
        );
    }
}

<?php

declare(strict_types=1);

namespace Capell\PublicActions\Actions;

use Capell\PublicActions\Data\PublicActionDispatchResultData;
use Capell\PublicActions\Enums\PublicActionDispatchStatus;
use Capell\PublicActions\Models\PublicActionDestination;
use Capell\PublicActions\Models\PublicActionDispatchAttempt;
use Capell\PublicActions\Models\PublicActionSubmission;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

final class ReplayPublicActionDispatchAttemptAction
{
    use AsAction;

    public function handle(PublicActionDispatchAttempt $attempt): PublicActionDispatchResultData
    {
        throw_unless(in_array($attempt->status, [PublicActionDispatchStatus::Pending, PublicActionDispatchStatus::Retryable, PublicActionDispatchStatus::Failed], true), RuntimeException::class, 'Only pending, retryable, or failed dispatch attempts can be replayed.');

        $destination = $attempt->destination;
        $submission = $attempt->submission;

        throw_if(! $destination instanceof PublicActionDestination || ! $submission instanceof PublicActionSubmission, RuntimeException::class, 'Dispatch attempt cannot be replayed because its destination or submission is missing.');

        return DispatchPublicActionDestinationAction::run($destination, $submission);
    }
}

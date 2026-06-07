<?php

declare(strict_types=1);

namespace Capell\PublicActions\Tests\Fakes;

use Capell\PublicActions\Contracts\PublicActionDestinationAdapter;
use Capell\PublicActions\Data\PublicActionDispatchResultData;
use Capell\PublicActions\Models\PublicActionDestination;
use Capell\PublicActions\Models\PublicActionDispatchAttempt;
use Capell\PublicActions\Models\PublicActionSubmission;

final class FakePublicActionDestinationAdapter implements PublicActionDestinationAdapter
{
    public function dispatch(
        PublicActionDestination $destination,
        PublicActionSubmission $submission,
        ?PublicActionDispatchAttempt $attempt = null,
    ): PublicActionDispatchResultData {
        return new PublicActionDispatchResultData(
            success: true,
            responseStatus: 202,
            responseSummary: $destination->adapter . ':' . $submission->getKey(),
        );
    }
}

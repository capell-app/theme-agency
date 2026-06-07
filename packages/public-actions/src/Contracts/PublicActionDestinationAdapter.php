<?php

declare(strict_types=1);

namespace Capell\PublicActions\Contracts;

use Capell\PublicActions\Data\PublicActionDispatchResultData;
use Capell\PublicActions\Models\PublicActionDestination;
use Capell\PublicActions\Models\PublicActionDispatchAttempt;
use Capell\PublicActions\Models\PublicActionSubmission;

interface PublicActionDestinationAdapter
{
    public function dispatch(
        PublicActionDestination $destination,
        PublicActionSubmission $submission,
        ?PublicActionDispatchAttempt $attempt = null,
    ): PublicActionDispatchResultData;
}

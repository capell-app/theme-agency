<?php

declare(strict_types=1);

namespace Capell\PublicActions\Actions;

use Capell\PublicActions\Contracts\PublicActionDestinationAdapter;
use Capell\PublicActions\Data\PublicActionDispatchResultData;
use Capell\PublicActions\Models\PublicActionDestination;
use Capell\PublicActions\Models\PublicActionDispatchAttempt;
use Capell\PublicActions\Models\PublicActionSubmission;
use Capell\PublicActions\Support\PublicActionDestinationAdapterRegistry;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;

final class DispatchPublicActionDestinationAction
{
    use AsAction;

    public function __construct(
        private readonly PublicActionDestinationAdapterRegistry $adapters,
    ) {}

    public function handle(
        PublicActionDestination $destination,
        PublicActionSubmission $submission,
        ?PublicActionDispatchAttempt $attempt = null,
    ): PublicActionDispatchResultData {
        $adapter = $this->adapters->resolve($destination->adapter);

        throw_unless($adapter instanceof PublicActionDestinationAdapter, InvalidArgumentException::class, sprintf('Public action destination adapter [%s] is not registered.', $destination->adapter));

        return $adapter->dispatch($destination, $submission, $attempt);
    }
}

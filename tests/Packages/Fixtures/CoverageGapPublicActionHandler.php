<?php

declare(strict_types=1);

namespace Capell\Tests\Packages\Fixtures;

use Capell\PublicActions\Contracts\PublicActionHandler;
use Capell\PublicActions\Data\PublicActionResultData;
use Capell\PublicActions\Data\PublicActionSubmissionData;

final class CoverageGapPublicActionHandler implements PublicActionHandler
{
    public function handle(PublicActionSubmissionData $submission): PublicActionResultData
    {
        $payload = $submission->payload->values;

        return new PublicActionResultData(
            success: true,
            message: sprintf('%s:%s', $payload['email'] ?? '', $payload['plan'] ?? ''),
            redirectUrl: 'https://example.test/thanks',
        );
    }
}

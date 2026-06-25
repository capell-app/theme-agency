<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions\Discovery;

use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecData;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Normalise + validate a candidate site spec without building anything. Lets the
 * external agent check its assembled spec before calling build/apply. Returns a
 * structured verdict rather than throwing, so a bad spec is a usable answer.
 *
 * @method static array{valid: bool, errors: array<string, mixed>, normalized: array<string, mixed>|null} run(array<string, mixed> $payload)
 */
final class ValidateSiteSpecAction
{
    use AsObject;

    /**
     * @param  array<string, mixed>  $payload
     * @return array{valid: bool, errors: array<string, mixed>, normalized: array<string, mixed>|null}
     */
    public function handle(array $payload): array
    {
        try {
            $spec = CapellSiteSpecData::validateAndCreate($payload);

            return [
                'valid' => true,
                'errors' => [],
                'normalized' => $spec->toArray(),
            ];
        } catch (ValidationException $exception) {
            return [
                'valid' => false,
                'errors' => $exception->errors(),
                'normalized' => null,
            ];
        }
    }
}

<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions\Discovery;

use Capell\AiCreator\Support\CapellInterviewScript;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Return the deterministic interview script. Read-only and input-free; the
 * agent reads this once to know what to ask and in what order.
 *
 * @method static array<string, mixed> run()
 */
final class GetInterviewAction
{
    use AsObject;

    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        return CapellInterviewScript::toArray();
    }
}

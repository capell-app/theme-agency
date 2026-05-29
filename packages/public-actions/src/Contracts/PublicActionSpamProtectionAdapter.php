<?php

declare(strict_types=1);

namespace Capell\PublicActions\Contracts;

use Capell\PublicActions\Data\PublicActionSpamProtectionResultData;
use Illuminate\Http\Request;

interface PublicActionSpamProtectionAdapter
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function check(array $input, ?Request $request = null): PublicActionSpamProtectionResultData;
}

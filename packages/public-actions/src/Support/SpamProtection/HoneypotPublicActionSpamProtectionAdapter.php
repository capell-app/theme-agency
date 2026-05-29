<?php

declare(strict_types=1);

namespace Capell\PublicActions\Support\SpamProtection;

use Capell\PublicActions\Contracts\PublicActionSpamProtectionAdapter;
use Capell\PublicActions\Data\PublicActionSpamProtectionResultData;
use Illuminate\Http\Request;

final class HoneypotPublicActionSpamProtectionAdapter implements PublicActionSpamProtectionAdapter
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function check(array $input, ?Request $request = null): PublicActionSpamProtectionResultData
    {
        $fields = config('capell-public-actions.spam_protection.honeypot.fields', ['_hp']);

        if (! is_array($fields)) {
            return new PublicActionSpamProtectionResultData(passed: true);
        }

        foreach ($fields as $field) {
            if (! is_string($field)) {
                continue;
            }

            if ($field === '') {
                continue;
            }

            $value = $input[$field] ?? null;
            if ($value === null) {
                continue;
            }

            if ($value === '') {
                continue;
            }

            return new PublicActionSpamProtectionResultData(
                passed: false,
                field: $field,
                message: __('capell-public-actions::generic.spam_detected'),
            );
        }

        return new PublicActionSpamProtectionResultData(passed: true);
    }
}

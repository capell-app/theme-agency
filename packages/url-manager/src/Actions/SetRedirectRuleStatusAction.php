<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Enums\RedirectRuleStatus;
use Capell\UrlManager\Models\RedirectRule;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class SetRedirectRuleStatusAction
{
    use AsAction;

    /**
     * @param  RedirectRule|Collection<int, RedirectRule>  $redirectRules
     */
    public function handle(RedirectRule|Collection $redirectRules, RedirectRuleStatus $status): int
    {
        $collection = $redirectRules instanceof RedirectRule ? collect([$redirectRules]) : $redirectRules;
        $updated = 0;

        $collection
            ->filter(fn (RedirectRule $redirectRule): bool => $redirectRule->status !== $status)
            ->each(function (RedirectRule $redirectRule) use ($status, &$updated): void {
                $redirectRule->forceFill(['status' => $status->value])->save();
                $updated++;
            });

        return $updated;
    }
}

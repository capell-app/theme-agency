<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Models\RedirectRule;
use Lorisleiva\Actions\Concerns\AsAction;

final class DeleteRedirectRuleAction
{
    use AsAction;

    public function handle(RedirectRule $redirectRule): void
    {
        $redirectRule->delete();
    }
}

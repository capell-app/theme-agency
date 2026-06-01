<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Enums\NewsletterSendStatus;
use Capell\Newsletter\Models\NewsletterSend;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildDueNewsletterSendsAction
{
    use AsAction;

    /**
     * @return Collection<int, NewsletterSend>
     */
    public function handle(?CarbonInterface $now = null, int $limit = 50): Collection
    {
        $now = $now instanceof CarbonInterface ? CarbonImmutable::instance($now) : CarbonImmutable::now();

        return NewsletterSend::query()
            ->where('status', NewsletterSendStatus::Scheduled)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->oldest('scheduled_at')
            ->limit(max(1, $limit))
            ->get();
    }
}

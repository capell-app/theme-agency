<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Jobs;

use Capell\SeoSuite\Actions\RunPageSpeedAuditAction;
use Capell\SeoSuite\Enums\PageSpeedAuditTriggerEnum;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as FoundationQueueable;

final class RunPageSpeedAuditJob implements ShouldQueue
{
    use FoundationQueueable;

    /**
     * @param  list<PageSpeedStrategyEnum>  $strategies
     */
    public function __construct(
        private readonly ?int $siteId = null,
        private readonly ?int $languageId = null,
        private readonly ?int $pageId = null,
        private readonly array $strategies = [],
        private readonly ?int $limit = null,
        private readonly bool $notify = false,
    ) {}

    public function handle(): void
    {
        RunPageSpeedAuditAction::run(
            trigger: $this->notify ? PageSpeedAuditTriggerEnum::Scheduled : PageSpeedAuditTriggerEnum::Manual,
            siteId: $this->siteId,
            languageId: $this->languageId,
            pageId: $this->pageId,
            strategies: $this->strategies,
            limit: $this->limit,
            notify: $this->notify,
        );
    }
}

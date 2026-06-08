<?php

declare(strict_types=1);

namespace Capell\PublishingStudio;

use Capell\PublishingStudio\Actions\RunDueSchedulerEventsAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Backwards-compatible scheduler entry point. The durable scheduler events
 * now own publish, public-expiry, and review reminder execution.
 */
class PublishScheduledPublishingStudioJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        RunDueSchedulerEventsAction::run(limit: 50, includePublish: true);
    }
}

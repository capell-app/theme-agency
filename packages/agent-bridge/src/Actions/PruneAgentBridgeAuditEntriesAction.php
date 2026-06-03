<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Actions;

use Capell\AgentBridge\Models\CapellAgentBridgeAuditEntry;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(int $days)
 */
final class PruneAgentBridgeAuditEntriesAction
{
    use AsAction;

    public function handle(int $days): int
    {
        $cutoff = CarbonImmutable::now()->subDays(max(1, $days));

        return CapellAgentBridgeAuditEntry::query()
            ->where('created_at', '<', $cutoff)
            ->delete();
    }
}

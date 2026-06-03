<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Actions;

use Capell\AgentBridge\Models\CapellAgentBridgeAuditEntry;
use Capell\AgentBridge\Models\CapellAgentBridgeToken;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Collection<int, array<string, mixed>> run(?CapellAgentBridgeToken $token = null, ?string $capability = null, ?string $event = null, int $limit = 25)
 */
final class QueryAgentBridgeAuditEntriesAction
{
    use AsAction;

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function handle(
        ?CapellAgentBridgeToken $token = null,
        ?string $capability = null,
        ?string $event = null,
        int $limit = 25,
    ): Collection {
        $query = DB::table((new CapellAgentBridgeAuditEntry)->getTable());

        if ($token instanceof CapellAgentBridgeToken) {
            $query->where('agent_bridge_token_id', $token->getKey());
        }

        if (is_string($capability) && $capability !== '') {
            $query->where('capability_key', $capability);
        }

        if (is_string($event) && $event !== '') {
            $query->where('event', $event);
        }

        return $query
            ->latest('created_at')
            ->limit(min(max($limit, 1), 100))
            ->get()
            ->map(static fn (object $entry): array => [
                'id' => (int) $entry->id,
                'event' => $entry->event,
                'capability' => $entry->capability_key,
                'scope' => $entry->scope,
                'tokenId' => $entry->agent_bridge_token_id,
                'payload' => self::jsonObject($entry->payload ?? null),
                'result' => self::jsonObject($entry->result ?? null),
                'createdAt' => $entry->created_at,
            ]);
    }

    /**
     * @return array<array-key, mixed>|null
     */
    private static function jsonObject(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }
}

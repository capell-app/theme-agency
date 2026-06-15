<?php

declare(strict_types=1);

namespace Capell\AccessGate\Data;

final readonly class PrunedAccessGateRecordsData
{
    public function __construct(
        public int $browserTokens,
        public int $claimTokens,
        public int $registrations,
        public int $events,
    ) {}

    public function total(): int
    {
        return $this->browserTokens
            + $this->claimTokens
            + $this->registrations
            + $this->events;
    }

    /**
     * @return array{browser_tokens: int, claim_tokens: int, registrations: int, events: int, total: int}
     */
    public function toArray(): array
    {
        return [
            'browser_tokens' => $this->browserTokens,
            'claim_tokens' => $this->claimTokens,
            'registrations' => $this->registrations,
            'events' => $this->events,
            'total' => $this->total(),
        ];
    }
}

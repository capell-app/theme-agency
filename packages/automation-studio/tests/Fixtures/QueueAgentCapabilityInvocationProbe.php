<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Tests\Fixtures;

use Capell\AgentBridge\Data\AuthenticatedAgentBridgeClientData;

final class QueueAgentCapabilityInvocationProbe
{
    public ?string $capabilityKey = null;

    /** @var array<string, mixed> */
    public array $payload = [];

    public ?AuthenticatedAgentBridgeClientData $client = null;
}

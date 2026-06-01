<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Actions\Pages;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AgentBridge\Support\AgentBridgePageAccess;
use Carbon\CarbonInterface;

final class DisablePageCapabilityAction implements CapellAgentBridgeCapabilityAction
{
    public function preview(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $payload = $this->validatedPayload($invocation->payload);
        AgentBridgePageAccess::authorizedPage($invocation->user, (int) $payload['page_id']);

        return new CapabilityResultData(
            ok: true,
            message: 'The page visibility window will be ended immediately.',
            data: [
                'page_id' => $payload['page_id'],
                'visible_until' => now()->toIso8601String(),
            ],
        );
    }

    public function execute(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $payload = $this->validatedPayload($invocation->payload);
        $page = AgentBridgePageAccess::authorizedPage($invocation->user, (int) $payload['page_id']);
        $page->forceFill(['visible_until' => now()])->save();
        $visibleUntil = $page->getAttribute('visible_until');

        return new CapabilityResultData(
            ok: true,
            message: 'Page has been disabled.',
            data: [
                'page_id' => (int) $page->getKey(),
                'visible_until' => $visibleUntil instanceof CarbonInterface ? $visibleUntil->toIso8601String() : $visibleUntil,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function validatedPayload(array $payload): array
    {
        validator($payload, [
            'page_id' => ['required', 'integer'],
        ])->validate();

        return $payload;
    }
}

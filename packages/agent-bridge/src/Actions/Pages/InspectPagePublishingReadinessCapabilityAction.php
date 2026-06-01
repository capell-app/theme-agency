<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Actions\Pages;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AgentBridge\Support\AgentBridgePageAccess;
use Illuminate\Support\Collection;

final class InspectPagePublishingReadinessCapabilityAction implements CapellAgentBridgeCapabilityAction
{
    public function preview(CapabilityInvocationData $invocation): CapabilityResultData
    {
        return $this->execute($invocation);
    }

    public function execute(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $payload = $this->validatedPayload($invocation->payload);
        $page = AgentBridgePageAccess::authorizedPage($invocation->user, (int) $payload['page_id'], ['site', 'type', 'layout', 'pageUrls']);
        $pageUrls = $page->getAttribute('pageUrls');

        $checks = [
            'has_site' => $page->getAttribute('site') !== null,
            'has_type' => $page->getAttribute('type') !== null,
            'has_layout' => $page->getAttribute('layout') !== null,
            'has_page_urls' => $pageUrls instanceof Collection && $pageUrls->isNotEmpty(),
            'is_visible_now' => method_exists($page, 'isVisible') ? (bool) $page->isVisible() : null,
        ];

        return new CapabilityResultData(
            ok: ! in_array(false, $checks, true),
            message: 'Page publishing readiness has been inspected.',
            data: [
                'page_id' => (int) $page->getKey(),
                'name' => $page->getAttribute('name'),
                'checks' => $checks,
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

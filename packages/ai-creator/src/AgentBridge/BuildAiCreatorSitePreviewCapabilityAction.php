<?php

declare(strict_types=1);

namespace Capell\AiCreator\AgentBridge;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AiCreator\Actions\BuildAiCreatorSitePreviewAction;
use Capell\AiCreator\Actions\Discovery\ValidateSiteSpecAction;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecData;
use Capell\AiCreator\Models\AiCreatorSession;
use Capell\AiCreator\Support\AiCreatorSessionAccess;
use Illuminate\Support\Arr;

/**
 * build_preview: materialise a non-destructive preview site for a session.
 *
 * preview() is a dry run — it authorises, validates the spec, and reports what
 * WOULD be built without writing anything. execute() builds the flagged
 * preview site and stores the spec on the session. This mirrors the rule that
 * the agent always validate_spec + build_preview before any irreversible
 * deploy.
 */
final class BuildAiCreatorSitePreviewCapabilityAction implements CapellAgentBridgeCapabilityAction
{
    public function preview(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $this->authorize($invocation);
        $verdict = ValidateSiteSpecAction::run($this->specPayload($invocation));

        return new CapabilityResultData(
            ok: $verdict['valid'],
            message: $verdict['valid'] ? 'Preview is ready to build.' : 'Spec failed validation.',
            data: ['would_build' => $verdict['valid'], 'validation' => $verdict],
        );
    }

    public function execute(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $session = $this->authorize($invocation);
        $spec = CapellSiteSpecData::validateAndCreate($this->specPayload($invocation));

        $session = BuildAiCreatorSitePreviewAction::run($session, $spec);

        return new CapabilityResultData(
            ok: true,
            message: 'Preview site built.',
            data: $session->preview_output ?? [],
        );
    }

    private function authorize(CapabilityInvocationData $invocation): AiCreatorSession
    {
        $session = $this->sessionFromPayload($invocation->payload);
        AiCreatorSessionAccess::authorizeSession($invocation->user, $session);

        return $session;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sessionFromPayload(array $payload): AiCreatorSession
    {
        $validated = validator($payload, [
            'session_id' => ['required', 'integer'],
            'spec' => ['required', 'array'],
        ])->validate();

        $sessionId = Arr::get($validated, 'session_id');

        return AiCreatorSession::query()->findOrFail(is_numeric($sessionId) ? (int) $sessionId : 0);
    }

    /**
     * @return array<string, mixed>
     */
    private function specPayload(CapabilityInvocationData $invocation): array
    {
        $spec = Arr::get($invocation->payload, 'spec', []);

        return is_array($spec) ? $spec : [];
    }
}

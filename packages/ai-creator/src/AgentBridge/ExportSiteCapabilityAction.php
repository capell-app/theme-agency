<?php

declare(strict_types=1);

namespace Capell\AiCreator\AgentBridge;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AiCreator\Actions\Discovery\ValidateSiteSpecAction;
use Capell\AiCreator\Actions\ExportSiteAction;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecData;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

/**
 * export_site: write a portable spec artifact for a local Capell project.
 *
 * preview() is a dry run (validate only). execute() validates, writes the spec
 * JSON to the local disk under ai-creator/exports, and returns the path + the
 * `capell:install --spec=` command. Cost-free and creates no cloud resources.
 */
final class ExportSiteCapabilityAction implements CapellAgentBridgeCapabilityAction
{
    public function preview(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $verdict = ValidateSiteSpecAction::run($this->specPayload($invocation));

        return new CapabilityResultData(
            ok: $verdict['valid'],
            message: $verdict['valid'] ? 'Spec is ready to export.' : 'Spec failed validation.',
            data: ['would_export' => $verdict['valid'], 'validation' => $verdict],
        );
    }

    public function execute(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $spec = CapellSiteSpecData::validateAndCreate($this->specPayload($invocation));
        $requestedName = Arr::get($invocation->payload, 'project_name', $spec->site->name);
        $projectName = is_string($requestedName) ? $requestedName : $spec->site->name;

        $artifact = ExportSiteAction::run($spec, $projectName);

        $path = 'ai-creator/exports/' . $artifact['filename'];
        Storage::disk('local')->put(
            $path,
            (string) json_encode($artifact['spec'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );

        return new CapabilityResultData(
            ok: true,
            message: 'Site spec exported.',
            data: [
                'project' => $artifact['project'],
                'path' => $path,
                'install_command' => $artifact['install_command'],
            ],
        );
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

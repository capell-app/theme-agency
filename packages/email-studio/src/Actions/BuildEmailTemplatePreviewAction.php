<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Data\EmailContextData;
use Capell\EmailStudio\Data\EmailTemplateDefinitionData;
use Capell\EmailStudio\Data\EmailTemplatePreviewData;
use Capell\EmailStudio\Data\RenderedEmailData;
use Capell\EmailStudio\Models\EmailTemplateRegistration;
use Capell\EmailStudio\Support\EmailTemplateRegistry;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class BuildEmailTemplatePreviewAction
{
    use AsAction;

    public function handle(EmailTemplateRegistration $registration): EmailTemplatePreviewData
    {
        $definition = resolve(EmailTemplateRegistry::class)->findDefinition($registration->template_key);
        $sampleData = $definition instanceof EmailTemplateDefinitionData ? $definition->sampleVariables() : [];

        try {
            $rendered = RenderResolvedEmailTemplateAction::run(
                templateKey: $registration->template_key,
                context: new EmailContextData(variables: $sampleData, preview: true),
                siteId: $registration->site_id,
                siteScopeKey: $registration->site_scope_key,
            )->rendered;
        } catch (Throwable $throwable) {
            return new EmailTemplatePreviewData(
                rendered: null,
                sampleData: $sampleData,
                error: $throwable->getMessage(),
            );
        }

        return new EmailTemplatePreviewData(
            rendered: $rendered,
            sampleData: $sampleData,
            missingVariables: $this->missingVariables($rendered),
        );
    }

    /**
     * @return list<string>
     */
    private function missingVariables(RenderedEmailData $rendered): array
    {
        $renderedText = implode(' ', array_filter([
            $rendered->subject,
            $rendered->previewText,
            $rendered->html,
            $rendered->text,
        ]));

        preg_match_all('/{{\s*([A-Za-z_][A-Za-z0-9_]*(?:\.[A-Za-z_][A-Za-z0-9_]*)*)\s*}}/', $renderedText, $missingMatches);

        return array_values(array_unique($missingMatches[1]));
    }
}

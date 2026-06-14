<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Data\EmailContextData;
use Capell\EmailStudio\Data\RenderedEmailData;
use Capell\EmailStudio\Models\EmailTemplateVariant;
use Capell\EmailStudio\Support\EmailVariableRenderer;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static RenderedEmailData run(EmailTemplateVariant $variant, EmailContextData $context)
 */
class RenderEmailTemplateAction
{
    use AsAction;

    public function handle(EmailTemplateVariant $variant, EmailContextData $context): RenderedEmailData
    {
        $variant->loadMissing(['template', 'theme']);

        $declaredVariables = $variant->template?->variables;
        $declaredVariables = is_array($declaredVariables) ? array_values($declaredVariables) : [];

        $renderer = resolve(EmailVariableRenderer::class);

        $rendered = new RenderedEmailData(
            subject: $renderer->renderSubject($variant->subject, $context->variables, $declaredVariables, $context->preview),
            previewText: $renderer->renderEscapedText($variant->preview_text, $context->variables, $declaredVariables, $context->preview),
            html: $renderer->renderHtml($variant->html_body, $context->variables, $declaredVariables, $context->preview),
            text: $renderer->renderText($variant->text_body, $context->variables, $declaredVariables, $context->preview),
        );

        if ($variant->theme === null) {
            return $rendered;
        }

        return new RenderedEmailData(
            subject: $rendered->subject,
            previewText: $rendered->previewText,
            html: ApplyEmailTemplateThemeAction::run($rendered->html, $variant->theme, $rendered->previewText),
            text: $rendered->text,
            headers: $rendered->headers,
        );
    }
}

<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Data\EmailContextData;
use Capell\EmailStudio\Data\EmailTemplateDefinitionData;
use Capell\EmailStudio\Data\RenderedEmailData;
use Capell\EmailStudio\Models\EmailTemplateTheme;
use Capell\EmailStudio\Support\EmailVariableRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static RenderedEmailData run(EmailTemplateDefinitionData $definition, EmailContextData $context)
 */
class RenderEmailTemplateDefinitionAction
{
    use AsAction;

    public function handle(EmailTemplateDefinitionData $definition, EmailContextData $context): RenderedEmailData
    {
        $renderer = resolve(EmailVariableRenderer::class);
        $declaredVariables = $definition->variableRequirements();

        $rendered = new RenderedEmailData(
            subject: $renderer->renderSubject($this->subject($definition), $context->variables, $declaredVariables, $context->preview),
            previewText: $renderer->renderEscapedText($definition->previewText, $context->variables, $declaredVariables, $context->preview),
            html: $renderer->renderHtml($this->html($definition, $context), $context->variables, $declaredVariables, $context->preview),
            text: $renderer->renderText($this->text($definition, $context), $context->variables, $declaredVariables, $context->preview),
        );

        $theme = $this->theme($definition, $context);

        if ($theme === null) {
            return $rendered;
        }

        return new RenderedEmailData(
            subject: $rendered->subject,
            previewText: $rendered->previewText,
            html: ApplyEmailTemplateThemeAction::run($rendered->html, $theme, $rendered->previewText),
            text: $rendered->text,
            headers: $rendered->headers,
        );
    }

    private function subject(EmailTemplateDefinitionData $definition): string
    {
        return $definition->subject ?? $definition->name;
    }

    private function html(EmailTemplateDefinitionData $definition, EmailContextData $context): string
    {
        if ($definition->html !== null) {
            return $definition->html;
        }

        if ($definition->htmlView !== null) {
            return View::make($definition->htmlView, [
                'definition' => $definition,
                'variables' => $context->variables,
            ])->render();
        }

        return '';
    }

    private function text(EmailTemplateDefinitionData $definition, EmailContextData $context): ?string
    {
        if ($definition->text !== null) {
            return $definition->text;
        }

        if ($definition->textView !== null) {
            return View::make($definition->textView, [
                'definition' => $definition,
                'variables' => $context->variables,
            ])->render();
        }

        return null;
    }

    private function theme(EmailTemplateDefinitionData $definition, EmailContextData $context): ?EmailTemplateTheme
    {
        if ($definition->defaultThemeKey === null) {
            return null;
        }

        if (! Schema::hasTable((new EmailTemplateTheme)->getTable())) {
            return null;
        }

        $siteScopeKey = $context->metadata['site_scope_key'] ?? 'global';
        $siteScopeKey = is_string($siteScopeKey) && $siteScopeKey !== '' ? $siteScopeKey : 'global';

        $theme = EmailTemplateTheme::query()
            ->where('key', $definition->defaultThemeKey)
            ->whereIn('site_scope_key', [$siteScopeKey, 'global'])
            ->orderByRaw('case when site_scope_key = ? then 0 else 1 end', [$siteScopeKey])
            ->when($siteScopeKey !== 'global', fn (Builder $query): Builder => $query->orderByDesc('site_id'))
            ->first();

        return $theme instanceof EmailTemplateTheme ? $theme : null;
    }
}

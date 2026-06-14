<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Data\EmailTemplateDefinitionData;
use Capell\EmailStudio\Enums\EmailTemplateStatus;
use Capell\EmailStudio\Enums\EmailVariantStatus;
use Capell\EmailStudio\Exceptions\EmailStudioSendingException;
use Capell\EmailStudio\Models\EmailTemplate;
use Capell\EmailStudio\Models\EmailTemplateTheme;
use Capell\EmailStudio\Models\EmailTemplateVariant;
use Capell\EmailStudio\Support\EmailTemplateRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EmailTemplateVariant run(string $templateKey, ?int $siteId = null, string $siteScopeKey = 'global', ?string $locale = null, ?int $emailProfileId = null)
 */
class CreateEmailTemplateOverrideAction
{
    use AsAction;

    public function handle(
        string $templateKey,
        ?int $siteId = null,
        string $siteScopeKey = 'global',
        ?string $locale = null,
        ?int $emailProfileId = null,
    ): EmailTemplateVariant {
        $definition = resolve(EmailTemplateRegistry::class)->findDefinition($templateKey, $locale ?? app()->getLocale());

        if (! $definition instanceof EmailTemplateDefinitionData) {
            throw EmailStudioSendingException::templateNotFound($templateKey, $siteScopeKey);
        }

        return DB::transaction(function () use ($definition, $siteId, $siteScopeKey, $locale, $emailProfileId): EmailTemplateVariant {
            /** @var EmailTemplate $template */
            $template = EmailTemplate::query()->updateOrCreate(
                [
                    'site_scope_key' => $siteScopeKey,
                    'key' => $definition->key,
                ],
                [
                    'site_id' => $siteId,
                    'name' => $definition->name,
                    'status' => EmailTemplateStatus::Approved,
                    'description' => $definition->description,
                    'variables' => $definition->variableNames(),
                ],
            );

            $latestVersion = $template->variants()
                ->where('site_scope_key', $siteScopeKey)
                ->max('version');
            $version = is_numeric($latestVersion) ? ((int) $latestVersion) + 1 : 1;

            /** @var EmailTemplateVariant $variant */
            $variant = $template->variants()->create([
                'site_id' => $siteId,
                'site_scope_key' => $siteScopeKey,
                'email_profile_id' => $emailProfileId,
                'email_template_theme_id' => $this->themeId($definition, $siteScopeKey),
                'locale' => $locale ?? $definition->defaultLocale,
                'status' => EmailVariantStatus::Draft,
                'version' => $version,
                'subject' => $definition->subject ?? $definition->name,
                'preview_text' => $definition->previewText,
                'cc' => $definition->cc,
                'bcc' => $definition->bcc,
                'html_body' => $this->html($definition),
                'text_body' => $this->text($definition),
                'approved_at' => null,
                'approved_by' => null,
            ]);

            return $variant;
        });
    }

    private function themeId(EmailTemplateDefinitionData $definition, string $siteScopeKey): ?int
    {
        if ($definition->defaultThemeKey === null) {
            return null;
        }

        $theme = EmailTemplateTheme::query()
            ->where('key', $definition->defaultThemeKey)
            ->whereIn('site_scope_key', [$siteScopeKey, 'global'])
            ->orderByRaw('case when site_scope_key = ? then 0 else 1 end', [$siteScopeKey])
            ->first();

        if (! $theme instanceof EmailTemplateTheme) {
            return null;
        }

        $themeKey = $theme->getKey();

        return is_numeric($themeKey) ? (int) $themeKey : null;
    }

    private function html(EmailTemplateDefinitionData $definition): string
    {
        if ($definition->html !== null) {
            return $definition->html;
        }

        if ($definition->htmlView !== null) {
            return View::make($definition->htmlView, [
                'definition' => $definition,
                'variables' => [],
            ])->render();
        }

        return '';
    }

    private function text(EmailTemplateDefinitionData $definition): ?string
    {
        if ($definition->text !== null) {
            return $definition->text;
        }

        if ($definition->textView !== null) {
            return View::make($definition->textView, [
                'definition' => $definition,
                'variables' => [],
            ])->render();
        }

        return null;
    }
}

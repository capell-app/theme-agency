<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Data\EmailContextData;
use Capell\EmailStudio\Data\ResolvedEmailTemplateData;
use Capell\EmailStudio\Enums\EmailTemplateStatus;
use Capell\EmailStudio\Exceptions\EmailStudioSendingException;
use Capell\EmailStudio\Models\EmailTemplate;
use Capell\EmailStudio\Models\EmailTemplateVariant;
use Capell\EmailStudio\Support\EmailTemplateRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static ResolvedEmailTemplateData run(string $templateKey, EmailContextData $context, ?int $siteId = null, string $siteScopeKey = 'global', ?string $locale = null)
 */
class RenderResolvedEmailTemplateAction
{
    use AsAction;

    public function handle(
        string $templateKey,
        EmailContextData $context,
        ?int $siteId = null,
        string $siteScopeKey = 'global',
        ?string $locale = null,
    ): ResolvedEmailTemplateData {
        $resolvedLocale = $locale ?? app()->getLocale();
        $template = $this->resolveTemplate($templateKey, $siteScopeKey);

        if ($template instanceof EmailTemplate) {
            $variant = ResolveEmailTemplateVariantAction::run($template, $siteScopeKey, $resolvedLocale);

            if ($variant instanceof EmailTemplateVariant) {
                return new ResolvedEmailTemplateData(
                    template: $template,
                    variant: $variant,
                    rendered: RenderEmailTemplateAction::run($variant, $context),
                    cc: $this->addressRows($variant->cc),
                    bcc: $this->addressRows($variant->bcc),
                );
            }
        }

        $definition = resolve(EmailTemplateRegistry::class)->findDefinition($templateKey, $resolvedLocale);

        if ($definition === null) {
            if ($template instanceof EmailTemplate) {
                throw EmailStudioSendingException::variantNotFound($templateKey, $siteScopeKey);
            }

            throw EmailStudioSendingException::templateNotFound($templateKey, $siteScopeKey);
        }

        return new ResolvedEmailTemplateData(
            template: $template,
            variant: null,
            rendered: RenderEmailTemplateDefinitionAction::run(
                definition: $definition,
                context: new EmailContextData(
                    variables: $context->variables,
                    preview: $context->preview,
                    metadata: [
                        ...$context->metadata,
                        'site_id' => $siteId,
                        'site_scope_key' => $siteScopeKey,
                        'locale' => $resolvedLocale,
                    ],
                ),
            ),
            cc: $definition->cc,
            bcc: $definition->bcc,
        );
    }

    private function resolveTemplate(string $templateKey, string $siteScopeKey): ?EmailTemplate
    {
        if (! Schema::hasTable((new EmailTemplate)->getTable())) {
            return null;
        }

        $template = EmailTemplate::query()
            ->where('key', $templateKey)
            ->where('status', EmailTemplateStatus::Approved)
            ->whereIn('site_scope_key', [$siteScopeKey, 'global'])
            ->orderByRaw('case when site_scope_key = ? then 0 else 1 end', [$siteScopeKey])
            ->when($siteScopeKey !== 'global', fn (Builder $query): Builder => $query->orderByDesc('site_id'))
            ->first();

        return $template instanceof EmailTemplate ? $template : null;
    }

    /**
     * @return array<int, array{email: string, name?: string|null}>
     */
    private function addressRows(mixed $addresses): array
    {
        if (! is_array($addresses)) {
            return [];
        }

        return collect($addresses)
            ->filter(static fn (mixed $address): bool => is_array($address) && is_string($address['email'] ?? null) && $address['email'] !== '')
            ->map(static fn (array $address): array => [
                'email' => (string) $address['email'],
                'name' => is_string($address['name'] ?? null) ? $address['name'] : null,
            ])
            ->values()
            ->all();
    }
}

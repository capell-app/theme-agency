<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Models\EmailTemplateRegistration;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EmailTemplateRegistration run(string $key, string $name, array<int, string> $variables, ?string $description = null, string $packageName = 'capell-app/email-studio', ?int $siteId = null, string $siteScopeKey = 'global', string $defaultLocale = 'en', bool $isStaticRenderable = false)
 */
class RegisterEmailTemplateAction
{
    use AsAction;

    /**
     * @param  array<int, string>  $variables
     */
    public function handle(
        string $key,
        string $name,
        array $variables,
        ?string $description = null,
        string $packageName = 'capell-app/email-studio',
        ?int $siteId = null,
        string $siteScopeKey = 'global',
        string $defaultLocale = 'en',
        bool $isStaticRenderable = false,
    ): EmailTemplateRegistration {
        $registration = EmailTemplateRegistration::query()
            ->where('site_scope_key', $siteScopeKey)
            ->where('package_name', $packageName)
            ->where('template_key', $key)
            ->first();

        $attributes = [
            'site_id' => $siteId,
            'site_scope_key' => $siteScopeKey,
            'package_name' => $packageName,
            'template_key' => $key,
            'name' => $name,
            'description' => $description,
            'variables' => array_values($variables),
            'default_locale' => $defaultLocale,
            'is_static_renderable' => $isStaticRenderable,
        ];

        if (! $registration instanceof EmailTemplateRegistration) {
            return EmailTemplateRegistration::query()->create($attributes);
        }

        $registration->fill($attributes);

        if ($registration->isDirty()) {
            $registration->save();
        }

        return $registration;
    }
}

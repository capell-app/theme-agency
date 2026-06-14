<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Support;

use Capell\EmailStudio\Actions\RegisterEmailTemplateAction;
use Capell\EmailStudio\Data\EmailTemplateDefinitionData;
use Capell\EmailStudio\Data\EmailTemplateVariableData;
use Capell\EmailStudio\Models\EmailTemplateRegistration;
use Illuminate\Support\Facades\Schema;

class EmailTemplateRegistry
{
    /**
     * @var array<string, array{key: string, name: string, variables: array<int, string>, description: string|null, packageName: string, siteId: int|null, siteScopeKey: string, defaultLocale: string, isStaticRenderable: bool}>
     */
    private array $registrations = [];

    /**
     * @var array<string, array<string, EmailTemplateDefinitionData>>
     */
    private array $definitions = [];

    /**
     * @param  array<int, string>  $variables
     */
    public function register(
        string $key,
        string $name,
        array $variables,
        ?string $description = null,
        string $packageName = 'capell-app/email-studio',
        ?int $siteId = null,
        string $siteScopeKey = 'global',
    ): self {
        $definition = new EmailTemplateDefinitionData(
            key: $key,
            packageName: $packageName,
            name: $name,
            description: $description,
            variables: array_map(
                static fn (string $variable): EmailTemplateVariableData => new EmailTemplateVariableData($variable),
                array_values($variables),
            ),
        );

        $this->queueRegistration(
            definition: $definition,
            variables: array_values($variables),
            siteId: $siteId,
            siteScopeKey: $siteScopeKey,
        );

        return $this;
    }

    public function registerDefinition(EmailTemplateDefinitionData $definition): self
    {
        $this->storeDefinition($definition);
        $this->queueRegistration($definition, $definition->variableNames());

        return $this;
    }

    public function findDefinition(string $key, ?string $locale = null): ?EmailTemplateDefinitionData
    {
        $definitions = $this->definitions[$key] ?? [];

        if ($locale !== null && isset($definitions[$locale]) && $definitions[$locale]->isRenderable()) {
            return $definitions[$locale];
        }

        if (isset($definitions['en']) && $definitions['en']->isRenderable()) {
            return $definitions['en'];
        }

        return collect($definitions)
            ->first(static fn (EmailTemplateDefinitionData $definition): bool => $definition->isRenderable());
    }

    /**
     * @return array<string, EmailTemplateDefinitionData>
     */
    public function definitions(?string $locale = null): array
    {
        $definitions = [];

        foreach (array_keys($this->definitions) as $key) {
            $definition = $this->findDefinition($key, $locale);

            if ($definition instanceof EmailTemplateDefinitionData) {
                $definitions[$key] = $definition;
            }
        }

        ksort($definitions);

        return $definitions;
    }

    /**
     * @return array<int, EmailTemplateRegistration>
     */
    public function persist(): array
    {
        if (! Schema::hasTable((new EmailTemplateRegistration)->getTable())) {
            return [];
        }

        return array_values(array_map(
            static fn (array $registration): EmailTemplateRegistration => RegisterEmailTemplateAction::run(...$registration),
            $this->registrations,
        ));
    }

    private function storeDefinition(EmailTemplateDefinitionData $definition): void
    {
        $this->definitions[$definition->key][$definition->defaultLocale] = $definition;
    }

    /**
     * @param  list<string>  $variables
     */
    private function queueRegistration(
        EmailTemplateDefinitionData $definition,
        array $variables,
        ?int $siteId = null,
        string $siteScopeKey = 'global',
    ): void {
        $registrationKey = implode('|', [
            $siteScopeKey,
            $definition->packageName,
            $definition->key,
        ]);

        $this->registrations[$registrationKey] = [
            'key' => $definition->key,
            'name' => $definition->name,
            'variables' => $variables,
            'description' => $definition->description,
            'packageName' => $definition->packageName,
            'siteId' => $siteId,
            'siteScopeKey' => $siteScopeKey,
            'defaultLocale' => $definition->defaultLocale,
            'isStaticRenderable' => $definition->isRenderable(),
        ];
    }
}

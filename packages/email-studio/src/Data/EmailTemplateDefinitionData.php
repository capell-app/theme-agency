<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Data;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;

class EmailTemplateDefinitionData extends Data
{
    /**
     * @param  list<EmailTemplateVariableData>  $variables
     * @param  array<string, mixed>  $sampleData
     * @param  array<int, array{email: string, name?: string|null}>  $cc
     * @param  array<int, array{email: string, name?: string|null}>  $bcc
     */
    public function __construct(
        public string $key,
        public string $packageName,
        public string $name,
        public ?string $description = null,
        public array $variables = [],
        public string $defaultLocale = 'en',
        public ?string $subject = null,
        public ?string $previewText = null,
        public ?string $html = null,
        public ?string $text = null,
        public ?string $htmlView = null,
        public ?string $textView = null,
        public ?string $defaultThemeKey = null,
        public array $sampleData = [],
        public array $cc = [],
        public array $bcc = [],
    ) {}

    /**
     * @return list<string>
     */
    public function variableNames(): array
    {
        return array_values(array_map(
            static fn (EmailTemplateVariableData $variable): string => $variable->name,
            $this->variables,
        ));
    }

    /**
     * @return array<string, bool>
     */
    public function variableRequirements(): array
    {
        return collect($this->variables)
            ->mapWithKeys(static fn (EmailTemplateVariableData $variable): array => [
                $variable->name => $variable->required,
            ])
            ->all();
    }

    public function isRenderable(): bool
    {
        return $this->html !== null
            || $this->htmlView !== null
            || $this->text !== null
            || $this->textView !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function sampleVariables(): array
    {
        $variables = $this->sampleData;

        foreach ($this->variables as $variable) {
            if ($variable->sampleValue === null || Arr::has($variables, $variable->name)) {
                continue;
            }

            Arr::set($variables, $variable->name, $variable->sampleValue);
        }

        return $variables;
    }
}

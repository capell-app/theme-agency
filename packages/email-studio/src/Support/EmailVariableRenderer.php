<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Support;

use Capell\EmailStudio\Exceptions\EmailTemplateRenderingException;
use Capell\EmailStudio\Settings\EmailStudioSettings;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Stringable;
use Throwable;

class EmailVariableRenderer
{
    private const string VARIABLE_PATTERN = '/{{\s*([A-Za-z_][A-Za-z0-9_]*(?:\.[A-Za-z_][A-Za-z0-9_]*)*)\s*}}/';

    /**
     * @param  array<string, mixed>  $variables
     * @param  array<int|string, bool|string>  $declaredVariables
     */
    public function renderHtml(string $template, array $variables, array $declaredVariables, bool $preview): string
    {
        return $this->render($template, $variables, $declaredVariables, $preview, escape: true);
    }

    /**
     * @param  array<string, mixed>  $variables
     * @param  array<int|string, bool|string>  $declaredVariables
     */
    public function renderSubject(string $template, array $variables, array $declaredVariables, bool $preview): string
    {
        $rendered = $this->render($template, $variables, $declaredVariables, $preview, escape: false);

        return Str::of($rendered)
            ->replaceMatches('/[\r\n\t]+/', ' ')
            ->replaceMatches('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/', '')
            ->squish()
            ->toString();
    }

    /**
     * @param  array<string, mixed>  $variables
     * @param  array<int|string, bool|string>  $declaredVariables
     */
    public function renderText(?string $template, array $variables, array $declaredVariables, bool $preview): ?string
    {
        if ($template === null) {
            return null;
        }

        return $this->render($template, $variables, $declaredVariables, $preview, escape: false);
    }

    /**
     * @param  array<string, mixed>  $variables
     * @param  array<int|string, bool|string>  $declaredVariables
     */
    public function renderEscapedText(?string $template, array $variables, array $declaredVariables, bool $preview): ?string
    {
        if ($template === null) {
            return null;
        }

        return $this->render($template, $variables, $declaredVariables, $preview, escape: true);
    }

    /**
     * @param  array<string, mixed>  $variables
     * @param  array<int|string, bool|string>  $declaredVariables
     */
    private function render(
        string $template,
        array $variables,
        array $declaredVariables,
        bool $preview,
        bool $escape,
    ): string {
        $variableRequirements = $this->variableRequirements($declaredVariables);
        $missingVariables = $this->missingVariables($template, $variables, $variableRequirements);

        if ($missingVariables !== [] && ! $preview) {
            throw EmailTemplateRenderingException::missingVariables($missingVariables);
        }

        return (string) preg_replace_callback(
            self::VARIABLE_PATTERN,
            function (array $matches) use ($variables, $variableRequirements, $escape): string {
                $variableName = $matches[1];

                $value = $this->resolveVariable($variableName, $variables, $variableRequirements);

                if ($value === null) {
                    return $matches[0];
                }

                return $escape ? e($value) : $value;
            },
            $template,
        );
    }

    /**
     * @param  array<string, mixed>  $variables
     * @param  array<string, bool>  $variableRequirements
     * @return array<int, string>
     */
    private function missingVariables(string $template, array $variables, array $variableRequirements): array
    {
        preg_match_all(self::VARIABLE_PATTERN, $template, $matches);

        $missingVariables = [];

        foreach ($matches[1] as $variableName) {
            if ($this->isMissingRequiredVariable($variableName, $variables, $variableRequirements)) {
                $missingVariables[] = $variableName;
            }
        }

        return array_values(array_unique($missingVariables));
    }

    /**
     * @param  array<string, mixed>  $variables
     * @param  array<string, bool>  $variableRequirements
     */
    private function resolveVariable(string $variableName, array $variables, array $variableRequirements): ?string
    {
        if (! array_key_exists($variableName, $variableRequirements)) {
            return null;
        }

        if (str_starts_with($variableName, 'config.')) {
            $value = $this->configVariableValue(substr($variableName, 7));

            return $value ?? ($variableRequirements[$variableName] ? null : '');
        }

        if (! Arr::has($variables, $variableName)) {
            return $variableRequirements[$variableName] ? null : '';
        }

        return $this->stringValue(Arr::get($variables, $variableName));
    }

    /**
     * @param  array<string, mixed>  $variables
     * @param  array<string, bool>  $variableRequirements
     */
    private function isMissingRequiredVariable(string $variableName, array $variables, array $variableRequirements): bool
    {
        if (! array_key_exists($variableName, $variableRequirements)) {
            return true;
        }

        if (! $variableRequirements[$variableName]) {
            return false;
        }

        if (str_starts_with($variableName, 'config.')) {
            return $this->configVariableValue(substr($variableName, 7)) === null;
        }

        return ! Arr::has($variables, $variableName);
    }

    private function configVariableValue(string $configKey): ?string
    {
        $allowedConfigKeys = $this->allowedConfigKeys();

        if (! in_array($configKey, $allowedConfigKeys, true)) {
            return null;
        }

        return $this->stringValue(config($configKey));
    }

    /**
     * @return list<string>
     */
    private function allowedConfigKeys(): array
    {
        try {
            if (app()->bound(EmailStudioSettings::class)) {
                /** @var EmailStudioSettings $settings */
                $settings = resolve(EmailStudioSettings::class);

                return array_values(array_filter(
                    $settings->template_config_variables,
                    static fn (mixed $key): bool => is_string($key) && $key !== '',
                ));
            }
        } catch (Throwable) {
            // Settings may not be migrated in early package boot or isolated unit tests.
        }

        $allowedConfigKeys = config('capell-email-studio.template_config_variables', []);

        if (! is_array($allowedConfigKeys)) {
            return [];
        }

        return array_values(array_filter(
            $allowedConfigKeys,
            static fn (mixed $key): bool => is_string($key) && $key !== '',
        ));
    }

    /**
     * @param  array<int|string, bool|string>  $declaredVariables
     * @return array<string, bool>
     */
    private function variableRequirements(array $declaredVariables): array
    {
        $requirements = [];

        foreach ($declaredVariables as $key => $value) {
            if (is_string($key)) {
                $requirements[$key] = is_bool($value) ? $value : true;

                continue;
            }

            if (is_string($value) && $value !== '') {
                $requirements[$value] = true;
            }
        }

        return $requirements;
    }

    private function stringValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value) || $value instanceof Stringable) {
            return (string) $value;
        }

        return '';
    }
}

<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Data\ConsentEvidenceData;
use Capell\Newsletter\Data\UtmAttributionData;
use Lorisleiva\Actions\Concerns\AsAction;

class ResolveUtmAttributionAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function handle(?ConsentEvidenceData $evidence = null, array $parameters = []): ?UtmAttributionData
    {
        $resolvedParameters = array_merge(
            $this->urlParameters($evidence?->url),
            $this->utmParameters($evidence->extra ?? []),
            $this->utmParameters($parameters),
        );

        $attribution = new UtmAttributionData(
            source: $this->stringValue($resolvedParameters, 'utm_source'),
            medium: $this->stringValue($resolvedParameters, 'utm_medium'),
            campaign: $this->stringValue($resolvedParameters, 'utm_campaign'),
            term: $this->stringValue($resolvedParameters, 'utm_term'),
            content: $this->stringValue($resolvedParameters, 'utm_content'),
            id: $this->stringValue($resolvedParameters, 'utm_id'),
        );

        return $attribution->hasValues() ? $attribution : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function urlParameters(?string $url): array
    {
        if ($url === null || $url === '') {
            return [];
        }

        $query = parse_url($url, PHP_URL_QUERY);

        if (! is_string($query) || $query === '') {
            return [];
        }

        $parameters = [];
        parse_str($query, $parameters);

        return $this->utmParameters($this->stringKeyedParameters($parameters));
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    private function utmParameters(array $parameters): array
    {
        return array_intersect_key($parameters, array_flip([
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'utm_id',
        ]));
    }

    /**
     * @param  array<int|string, mixed>  $parameters
     * @return array<string, mixed>
     */
    private function stringKeyedParameters(array $parameters): array
    {
        $stringKeyedParameters = [];

        foreach ($parameters as $key => $value) {
            if (is_string($key)) {
                $stringKeyedParameters[$key] = $value;
            }
        }

        return $stringKeyedParameters;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function stringValue(array $parameters, string $key): ?string
    {
        $value = $parameters[$key] ?? null;

        if (! is_scalar($value)) {
            return null;
        }

        $stringValue = trim((string) $value);

        return $stringValue === '' ? null : mb_substr($stringValue, 0, 255);
    }
}

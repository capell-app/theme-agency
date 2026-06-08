<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Data\RedirectImportResultData;
use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Enums\RedirectRuleStatus;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class ImportRedirectRulesAction
{
    use AsAction;

    /**
     * @param  iterable<int, array<string, mixed>>  $rows
     */
    public function handle(iterable $rows): RedirectImportResultData
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                UpsertRedirectRuleAction::run(new RedirectRuleData(
                    sourceUrl: $this->requiredString($row, 'source_url'),
                    targetUrl: $this->targetUrl($row),
                    siteId: $this->nullableInt($row['site_id'] ?? null),
                    languageId: $this->nullableInt($row['language_id'] ?? null),
                    statusCode: $this->nullableInt($row['status_code'] ?? null) ?? 301,
                    matchType: RedirectMatchType::from((string) ($row['match_type'] ?? RedirectMatchType::Exact->value)),
                    status: RedirectRuleStatus::from((string) ($row['status'] ?? RedirectRuleStatus::Active->value)),
                    priority: $this->nullableInt($row['priority'] ?? null) ?? 0,
                    preserveQuery: (bool) ($row['preserve_query'] ?? true),
                    notes: is_string($row['notes'] ?? null) ? $row['notes'] : null,
                ));

                $imported++;
            } catch (Throwable $throwable) {
                $skipped++;
                $errors[] = (string) __('capell-url-manager::validation.csv_row_error', [
                    'row' => $index + 1,
                    'message' => $throwable->getMessage(),
                ]);
            }
        }

        return new RedirectImportResultData($imported, $skipped, $errors);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function requiredString(array $row, string $key): string
    {
        $value = $row[$key] ?? null;

        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException((string) __('capell-url-manager::validation.csv_required_field', [
                'field' => $key,
            ]));
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function targetUrl(array $row): string
    {
        $statusCode = $this->nullableInt($row['status_code'] ?? null) ?? 301;

        if ($statusCode === 410) {
            $value = $row['target_url'] ?? null;

            return is_string($value) ? $value : '';
        }

        return $this->requiredString($row, 'target_url');
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        return is_numeric($value) ? (int) $value : null;
    }
}

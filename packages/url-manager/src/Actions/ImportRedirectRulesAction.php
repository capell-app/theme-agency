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
                    targetUrl: $this->requiredString($row, 'target_url'),
                    siteId: $this->nullableInt($row['site_id'] ?? null),
                    languageId: $this->nullableInt($row['language_id'] ?? null),
                    statusCode: $this->nullableInt($row['status_code'] ?? null) ?? 301,
                    matchType: RedirectMatchType::from((string) ($row['match_type'] ?? RedirectMatchType::Exact->value)),
                    status: RedirectRuleStatus::from((string) ($row['status'] ?? RedirectRuleStatus::Active->value)),
                    preserveQuery: (bool) ($row['preserve_query'] ?? true),
                    notes: is_string($row['notes'] ?? null) ? $row['notes'] : null,
                ));

                $imported++;
            } catch (Throwable $throwable) {
                $skipped++;
                $errors[] = sprintf('Row %d: %s', $index + 1, $throwable->getMessage());
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
            throw new InvalidArgumentException(sprintf('The %s field is required.', $key));
        }

        return $value;
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

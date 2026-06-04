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

final class PreviewRedirectRulesImportAction
{
    use AsAction;

    /**
     * @param  iterable<int, array<string, mixed>>  $rows
     */
    public function handle(iterable $rows): RedirectImportResultData
    {
        $valid = 0;
        $invalid = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                $this->validateRow($row);
                $valid++;
            } catch (Throwable $throwable) {
                $invalid++;
                $errors[] = sprintf('Row %d: %s', $index + 1, $throwable->getMessage());
            }
        }

        return new RedirectImportResultData($valid, $invalid, $errors);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function validateRow(array $row): void
    {
        $sourceUrl = $this->requiredString($row, 'source_url');
        $targetUrl = $this->requiredString($row, 'target_url');
        $matchType = RedirectMatchType::from((string) ($row['match_type'] ?? RedirectMatchType::Exact->value));
        $status = RedirectRuleStatus::from((string) ($row['status'] ?? RedirectRuleStatus::Active->value));

        $statusCode = is_numeric($row['status_code'] ?? null) ? (int) $row['status_code'] : 301;

        PrepareRedirectRuleDataAction::run(new RedirectRuleData(
            sourceUrl: $sourceUrl,
            targetUrl: $targetUrl,
            statusCode: $statusCode,
            matchType: $matchType,
            status: $status,
            priority: is_numeric($row['priority'] ?? null) ? (int) $row['priority'] : 0,
            preserveQuery: (bool) ($row['preserve_query'] ?? true),
            notes: is_string($row['notes'] ?? null) && trim($row['notes']) !== '' ? $row['notes'] : null,
        ));
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
}

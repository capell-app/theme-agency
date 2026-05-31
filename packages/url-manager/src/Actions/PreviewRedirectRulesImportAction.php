<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Data\RedirectImportResultData;
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
        RedirectRuleStatus::from((string) ($row['status'] ?? RedirectRuleStatus::Active->value));

        $statusCode = is_numeric($row['status_code'] ?? null) ? (int) $row['status_code'] : 301;

        throw_unless(in_array($statusCode, [301, 302, 307, 308], true), InvalidArgumentException::class, 'Redirect status code must be one of 301, 302, 307, or 308.');

        if ($matchType === RedirectMatchType::Regex) {
            throw_if(@preg_match(trim($sourceUrl), '') === false, InvalidArgumentException::class, 'Regex redirect sources must be valid PHP regular expressions.');

            return;
        }

        throw_if(
            NormalizeManagedUrlAction::run($sourceUrl) === NormalizeManagedUrlAction::run($targetUrl, allowAbsolute: true),
            InvalidArgumentException::class,
            'A redirect cannot point to itself.',
        );
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

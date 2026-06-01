<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Models\RedirectRule;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;

final class UpsertRedirectRuleAction
{
    use AsAction;

    public function handle(RedirectRuleData $data): RedirectRule
    {
        $sourceUrl = $data->matchType === RedirectMatchType::Regex
            ? $this->normalizeRegexSource($data->sourceUrl)
            : NormalizeManagedUrlAction::run($data->sourceUrl);
        $targetUrl = NormalizeManagedUrlAction::run($data->targetUrl, allowAbsolute: true);

        throw_if($data->matchType !== RedirectMatchType::Regex && $sourceUrl === $targetUrl, InvalidArgumentException::class, 'A redirect cannot point to itself.');

        throw_unless(in_array($data->statusCode, [301, 302, 307, 308], true), InvalidArgumentException::class, 'Redirect status code must be one of 301, 302, 307, or 308.');

        /** @var RedirectRule $redirectRule */
        $redirectRule = RedirectRule::query()->updateOrCreate(
            [
                'site_id' => $data->siteId,
                'language_id' => $data->languageId,
                'source_hash' => $this->hash($sourceUrl),
                'match_type' => $data->matchType->value,
            ],
            [
                'source_url' => $sourceUrl,
                'target_url' => $targetUrl,
                'target_hash' => $this->hash($targetUrl),
                'status_code' => $data->statusCode,
                'status' => $data->status->value,
                'preserve_query' => $data->preserveQuery,
                'notes' => $data->notes,
                'created_by_user_id' => $data->createdByUserId,
            ],
        );

        return $redirectRule;
    }

    private function hash(string $url): string
    {
        return hash('sha256', $url);
    }

    private function normalizeRegexSource(string $sourceUrl): string
    {
        $sourceUrl = trim($sourceUrl);

        throw_if($sourceUrl === '' || @preg_match($sourceUrl, '') === false, InvalidArgumentException::class, 'Regex redirect sources must be valid PHP regular expressions.');

        return $sourceUrl;
    }
}

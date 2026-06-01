<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\SeoSuite\Models\BrokenLink;
use Capell\UrlManager\Data\NotFoundOpportunityData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;

final class ImportSeoSuiteBrokenLinksAction
{
    use AsAction;

    private const string BROKEN_LINK_MODEL = BrokenLink::class;

    public function handle(?int $siteId = null, ?int $languageId = null, int $limit = 500): int
    {
        if (! class_exists(self::BROKEN_LINK_MODEL) || ! Schema::hasTable('broken_links')) {
            return 0;
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = self::BROKEN_LINK_MODEL;
        $imported = 0;

        $modelClass::query()
            ->where('http_status', '>=', 400)
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->each(function (Model $brokenLink) use (&$imported, $siteId, $languageId): void {
                $targetUrl = $brokenLink->getAttribute('target_url');

                if (! is_string($targetUrl) || trim($targetUrl) === '') {
                    return;
                }

                RecordNotFoundOpportunityAction::run(new NotFoundOpportunityData(
                    sourceUrl: $targetUrl,
                    siteId: $siteId,
                    languageId: $languageId,
                    context: [
                        'source' => 'seo_suite_broken_link',
                        'broken_link_id' => (int) $brokenLink->getKey(),
                        'page_id' => $this->nullableInt($brokenLink->getAttribute('page_id')),
                        'http_status' => $this->nullableInt($brokenLink->getAttribute('http_status')),
                    ],
                ));

                $imported++;
            });

        return $imported;
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

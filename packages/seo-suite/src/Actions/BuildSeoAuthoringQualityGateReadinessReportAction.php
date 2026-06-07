<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Models\Page;
use Capell\Core\Models\Translation;
use Capell\SeoSuite\Data\SeoAuthoringQualityGateReadinessRowData;
use Capell\SeoSuite\Data\SeoAuthoringQualityGateResultData;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static list<SeoAuthoringQualityGateReadinessRowData> run(?int $siteId = null, int $limit = 100)
 */
final class BuildSeoAuthoringQualityGateReadinessReportAction
{
    use AsAction;

    /**
     * @return list<SeoAuthoringQualityGateReadinessRowData>
     */
    public function handle(?int $siteId = null, int $limit = 100): array
    {
        $rows = [];

        Page::query()
            ->with([
                'translations.language',
                'type',
            ])
            ->when($siteId !== null, fn (BuilderContract $query): BuilderContract => $query->where('site_id', $siteId))
            ->limit($limit)
            ->get()
            ->each(function (Page $page) use (&$rows): void {
                foreach ($page->translations as $translation) {
                    if (! $translation instanceof Translation) {
                        continue;
                    }

                    $results = BuildSeoAuthoringQualityGateResultsAction::run(
                        formData: $this->formDataFor($page, $translation),
                        page: $page,
                        ignoreStrictSwitch: true,
                    );

                    $blockingMessages = collect($results)
                        ->filter(fn (SeoAuthoringQualityGateResultData $result): bool => $result->blocks())
                        ->map(fn (SeoAuthoringQualityGateResultData $result): string => $result->message)
                        ->unique()
                        ->values()
                        ->all();

                    if ($blockingMessages === []) {
                        continue;
                    }

                    $rows[] = new SeoAuthoringQualityGateReadinessRowData(
                        pageId: (int) $page->getKey(),
                        pageName: (string) $page->name,
                        languageId: (int) $translation->language_id,
                        languageName: (string) ($translation->language?->name ?? $translation->language_id),
                        messages: $blockingMessages,
                    );
                }
            });

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function formDataFor(Page $page, Translation $translation): array
    {
        return [
            'blueprint_id' => $page->blueprint_id,
            'translations' => [
                (string) $translation->getKey() => [
                    'language_id' => $translation->language_id,
                    'title' => $translation->title,
                    'meta' => (array) $translation->meta,
                ],
            ],
        ];
    }
}

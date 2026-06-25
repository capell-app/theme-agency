<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions;

use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecData;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecPageData;
use Capell\Core\Actions\CreateDefaultLanguagesAction;
use Capell\Core\Actions\CreateSiteAction;
use Capell\Core\Actions\CreateThemeAction;
use Capell\Core\Actions\SetupPageUrlsAction;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\Core\Support\Creator\PageCreator;
use Capell\FoundationTheme\Actions\InstallFoundationThemeLayoutDefaultsAction;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

/**
 * Builds a complete Capell site from a single CapellSiteSpecData contract.
 *
 * This is the one builder behind all three delivery targets (preview, cloud
 * deploy, local export). It performs NO inference of any kind: every value
 * originates in the spec the external agent assembled. Sections are
 * concatenated into each page's translation content HTML and rendered by the
 * foundation-theme `page-content` widget — the same guaranteed-render path the
 * foundation-theme demo installer uses. It builds on Capell\Core plus the
 * layout-builder + foundation-theme defaults (no AI/orchestration deps). The
 * structured sections[] is preserved upstream on the session plan columns;
 * promoting sections to real Section records is a documented multi-region
 * follow-on.
 *
 * Precondition: the install already has default layouts, the `page-content`
 * widget, and default page-types. This holds for any real cloud/local install;
 * in an empty test DB the caller must seed defaults first. The builder also
 * calls InstallFoundationThemeLayoutDefaultsAction (idempotent) to make the
 * precondition self-healing.
 *
 * @method static Site run(CapellSiteSpecData $spec)
 */
final class BuildCapellSiteFromSpecAction
{
    use AsAction;

    public function handle(CapellSiteSpecData $spec): Site
    {
        return DB::transaction(function () use ($spec): Site {
            $language = $this->resolveLanguage($spec);
            $languages = collect([$language]);

            $theme = $this->buildTheme($spec);

            // Self-healing precondition: ensure home/default layouts and the
            // page-content widget exist before any page references them.
            InstallFoundationThemeLayoutDefaultsAction::run();

            $site = CreateSiteAction::run($spec->site->name, null, $language, $languages, $theme);
            $this->applySiteMeta($site, $spec);

            $creator = resolve(PageCreator::class);

            foreach ($this->orderedPages($spec) as $index => $page) {
                $createdPage = $creator->createPage(
                    $this->pageData($page, $language, $index === 0),
                    $site,
                    $languages,
                );

                SetupPageUrlsAction::run($createdPage);
            }

            return $site->refresh();
        });
    }

    private function resolveLanguage(CapellSiteSpecData $spec): Language
    {
        $code = $spec->language->code;

        $language = Language::query()->where('code', $code)->first()
            ?? CreateDefaultLanguagesAction::run([$code])->first()
            ?? throw new RuntimeException("Language '{$code}' could not be resolved.");

        $language->forceFill([
            'name' => $spec->language->name,
            'locale' => $spec->language->locale,
            'flag' => $spec->language->flag,
            'default' => $spec->language->default,
        ])->save();

        return $language->refresh();
    }

    private function buildTheme(CapellSiteSpecData $spec): Theme
    {
        $theme = CreateThemeAction::run($spec->theme->key, ucfirst($spec->theme->key));

        $meta = is_array($theme->meta) ? $theme->meta : [];

        $colors = array_filter([
            'primary' => $spec->theme->colors->primary,
            'secondary' => $spec->theme->colors->secondary,
            'accent' => $spec->theme->colors->accent,
        ], static fn (?string $value): bool => $value !== null && $value !== '');

        if ($colors !== []) {
            $existingColors = $meta['colors'] ?? [];
            $meta['colors'] = array_merge(is_array($existingColors) ? $existingColors : [], $colors);
        }

        $branding = array_filter([
            'font_family' => $spec->theme->fontFamily,
            'link_color' => $spec->theme->linkColor,
            'link_color_active' => $spec->theme->linkColorActive,
            'container' => $spec->theme->container,
        ], static fn (?string $value): bool => $value !== null && $value !== '');

        $meta = array_merge($meta, $branding);

        $theme->meta = $meta;

        if ($spec->theme->customCss !== null && $spec->theme->customCss !== '') {
            $theme->custom_css = $spec->theme->customCss;
        }

        $theme->save();

        return $theme->refresh();
    }

    private function applySiteMeta(Site $site, CapellSiteSpecData $spec): void
    {
        $meta = $site->meta ?? [];

        $siteMeta = array_filter([
            'business_name' => $spec->site->businessName,
            'organization_type' => $spec->site->organisationType,
        ], static fn (?string $value): bool => $value !== null && $value !== '');

        if ($siteMeta === []) {
            return;
        }

        $site->meta = array_merge($meta, $siteMeta);
        $site->save();
    }

    /**
     * @return array<int, CapellSiteSpecPageData>
     */
    private function orderedPages(CapellSiteSpecData $spec): array
    {
        return collect($spec->pages)
            ->sortBy(static fn (CapellSiteSpecPageData $page): int => $page->order)
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function pageData(CapellSiteSpecPageData $page, Language $language, bool $isFirst): array
    {
        return [
            'name' => $page->name,
            'type_key' => $page->pageType,
            'layout_key' => $isFirst ? 'home' : 'default',
            'visible_from' => now()->subDay()->toDateString(),
            'meta' => array_merge($page->meta, ['visibility' => $page->visibility]),
            'translations' => [
                $language->code => [
                    'title' => $page->title,
                    'content' => $this->renderSections($page),
                    'summary' => $page->description,
                    'slug' => ltrim($page->slug, '/'),
                    'meta' => array_merge(['description' => $page->description], $page->meta),
                ],
            ],
        ];
    }

    /**
     * Concatenate the page's sections, in order, into a single HTML body. Each
     * section contributes an optional <h2> heading followed by its content.
     */
    private function renderSections(CapellSiteSpecPageData $page): string
    {
        return collect($page->sections)
            ->sortBy(fn ($section): int => $section->order)
            ->map(function ($section): string {
                $heading = $section->title !== null && $section->title !== ''
                    ? '<h2>' . e($section->title) . '</h2>'
                    : '';

                return $heading . $section->content;
            })
            ->implode('');
    }
}

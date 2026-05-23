<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Actions;

use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\Core\Models\Translation;
use Capell\FilamentPeek\Data\PagePreviewSnapshotData;
use Capell\Frontend\Actions\BuildPublicPageRenderDataAction;
use Capell\Frontend\Actions\BuildPublicRenderPerformanceReportAction;
use Capell\Frontend\Actions\ResolveFrontendRuntimeAction;
use Capell\Frontend\Contracts\FrontendContextReader;
use Capell\Frontend\Data\FrontendRenderContextData;
use Capell\Frontend\Support\CapellFrontendContext;
use Capell\Frontend\Support\Render\FrontendResponseRendererRegistry;
use Capell\Frontend\Support\State\FrontendState;
use Capell\Frontend\Support\View\ThemeChainResolver;
use Capell\Frontend\Support\View\ThemeViewRegistrar;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\WorkspaceContext;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class RenderPagePreviewSnapshotAction
{
    use AsAction;

    public function handle(PagePreviewSnapshotData $snapshot): Response
    {
        return $this->withWorkspaceContext($snapshot, fn (): Response => $this->renderSnapshot($snapshot));
    }

    private function renderSnapshot(PagePreviewSnapshotData $snapshot): Response
    {
        $page = Page::query()
            ->with([
                'layout.theme',
                'pageUrl.siteDomain',
                'pageUrls.siteDomain',
                'site.language',
                'site.siteDomains',
                'site.theme',
                'translation.language',
                'translations.language',
                'type',
            ])
            ->findOrFail($snapshot->pageId);

        Gate::authorize('update', $page);

        $previewPage = $this->previewPage($page, $snapshot);
        $site = $previewPage->site;
        $language = $previewPage->translation?->language ?? $site?->language;
        $layout = $previewPage->layout;
        $theme = $layout?->theme ?? $site?->theme;

        abort_unless($site instanceof Site, 404);
        abort_unless($language instanceof Language, 404);
        abort_unless($layout instanceof Layout, 404);

        $this->registerThemeViews($theme);

        $context = $this->seedFrontendContext($site, $language, $previewPage, $layout, $theme);
        $response = $this->render($context, $previewPage, $site, $language, $layout, $theme);

        return $response instanceof Response ? $response : $response->toResponse(request());
    }

    private function previewPage(Page $page, PagePreviewSnapshotData $snapshot): Page
    {
        $previewPage = clone $page;
        $previewPage->exists = true;
        $previewPage->forceFill($this->pageAttributes($snapshot->formState));

        $site = $this->resolveSite($page, $previewPage);
        $layout = $this->resolveLayout($page, $previewPage, $snapshot);
        $translations = $this->previewTranslations($page, $previewPage, $snapshot->formState);
        $translation = $this->currentTranslation($page, $translations);
        $pageUrls = $this->previewPageUrls($page, $translation);

        $previewPage->setRelation('site', $site);
        $previewPage->setRelation('layout', $layout);
        $previewPage->setRelation('translations', $translations);
        $previewPage->setRelation('translation', $translation);
        $previewPage->setRelation('pageUrls', $pageUrls);
        $previewPage->setRelation('pageUrl', $pageUrls->first());

        if ($page->relationLoaded('type')) {
            $previewPage->setRelation('type', $page->type);
        }

        return $previewPage;
    }

    /**
     * @param  array<string, mixed>  $formState
     * @return array<string, mixed>
     */
    private function pageAttributes(array $formState): array
    {
        return Arr::only($formState, [
            'admin',
            'layout_id',
            'meta',
            'name',
            'order',
            'parent_id',
            'uuid',
            'visible_from',
            'visible_until',
            'site_id',
            'blueprint_id',
        ]);
    }

    private function resolveSite(Page $page, Page $previewPage): ?Site
    {
        if ((int) $previewPage->site_id === (int) $page->site_id && $page->relationLoaded('site')) {
            return $page->site;
        }

        return Site::query()
            ->with(['language', 'siteDomains', 'theme'])
            ->find($previewPage->site_id);
    }

    private function resolveLayout(Page $page, Page $previewPage, PagePreviewSnapshotData $snapshot): ?Layout
    {
        $layoutId = $snapshot->layoutBuilderState?->layoutId ?? (int) $previewPage->layout_id;

        $layout = ((int) $page->layout_id === $layoutId && $page->relationLoaded('layout'))
            ? clone $page->layout
            : Layout::query()->with('theme')->find($layoutId);

        if (! $layout instanceof Layout) {
            return null;
        }

        if ($snapshot->layoutBuilderState !== null) {
            $layout->setAttribute('containers', $snapshot->layoutBuilderState->containers);
        }

        return $layout;
    }

    /**
     * @param  array<string, mixed>  $formState
     * @return EloquentCollection<int, Translation>
     */
    private function previewTranslations(Page $page, Page $previewPage, array $formState): EloquentCollection
    {
        $translations = $page->relationLoaded('translations')
            ? $page->translations->map(fn (Translation $translation): Translation => clone $translation)
            : collect();

        $stateTranslations = is_array($formState['translations'] ?? null) ? $formState['translations'] : [];

        foreach ($stateTranslations as $stateTranslation) {
            if (! is_array($stateTranslation)) {
                continue;
            }

            $languageId = isset($stateTranslation['language_id']) ? (int) $stateTranslation['language_id'] : null;

            if ($languageId === null) {
                continue;
            }

            $translation = $translations->first(
                fn (Translation $candidate): bool => (int) $candidate->language_id === $languageId,
            ) ?? new Translation;

            $translation->exists = true;
            $translation->forceFill(Arr::only($stateTranslation, ['content', 'language_id', 'meta', 'title']));
            $translation->setAttribute('translatable_type', $previewPage->getMorphClass());
            $translation->setAttribute('translatable_id', $previewPage->getKey());

            if (! $translation->relationLoaded('language')) {
                $language = Language::query()->find($languageId);
                if ($language instanceof Language) {
                    $translation->setRelation('language', $language);
                }
            }

            $translations = $translations->reject(
                fn (Translation $candidate): bool => (int) $candidate->language_id === $languageId,
            )->push($translation)->values();
        }

        return new EloquentCollection($translations->all());
    }

    /**
     * @param  EloquentCollection<int, Translation>  $translations
     */
    private function currentTranslation(Page $page, EloquentCollection $translations): ?Translation
    {
        $currentLanguageId = $page->translation?->language_id;

        if ($currentLanguageId !== null) {
            $translation = $translations->first(
                fn (Translation $candidate): bool => (int) $candidate->language_id === (int) $currentLanguageId,
            );

            if ($translation instanceof Translation) {
                return $translation;
            }
        }

        return $translations->first();
    }

    /**
     * @return EloquentCollection<int, PageUrl>
     */
    private function previewPageUrls(Page $page, ?Translation $translation): EloquentCollection
    {
        $pageUrls = $page->relationLoaded('pageUrls')
            ? $page->pageUrls->map(fn (PageUrl $pageUrl): PageUrl => clone $pageUrl)
            : collect();

        if ($translation instanceof Translation) {
            $pageUrls->each(function (PageUrl $pageUrl) use ($translation): void {
                if ((int) $pageUrl->language_id !== (int) $translation->language_id) {
                    return;
                }

                $pageUrl->setRelation('translation', $translation);
            });
        }

        return new EloquentCollection($pageUrls->all());
    }

    private function registerThemeViews(?Theme $theme): void
    {
        if (! $theme instanceof Theme || $theme->key === '') {
            return;
        }

        resolve(ThemeViewRegistrar::class)->register(
            resolve(ThemeChainResolver::class)->resolve($theme),
            $theme->key,
        );
    }

    private function seedFrontendContext(
        Site $site,
        Language $language,
        Page $page,
        Layout $layout,
        ?Theme $theme,
    ): FrontendState {
        $state = resolve(FrontendState::class)
            ->withSite($site)
            ->withLanguage($language)
            ->withPage($page)
            ->withLayout($layout)
            ->withParams([])
            ->withSlug(null);

        if ($theme instanceof Theme) {
            $state->withTheme($theme);
        }

        app()->instance(FrontendContextReader::class, $state);
        app()->forgetInstance(CapellFrontendContext::class);

        return $state;
    }

    private function render(
        FrontendContextReader $context,
        Page $page,
        Site $site,
        Language $language,
        Layout $layout,
        ?Theme $theme,
    ): Response|Responsable {
        $runtimeResolution = ResolveFrontendRuntimeAction::run($context);
        $renderContext = new FrontendRenderContextData(
            page: $page,
            site: $site,
            language: $language,
            layout: $layout,
            theme: $theme,
        );

        $renderer = resolve(FrontendResponseRendererRegistry::class)->forRuntime($runtimeResolution->runtime);

        if ($renderer === null) {
            throw new RuntimeException('No Capell frontend renderer is available for Filament Peek preview.');
        }

        $renderContext->runtimeManifest = $runtimeResolution->runtimeManifest;
        $renderContext->publicRenderData = BuildPublicPageRenderDataAction::run($renderContext);
        $context->setFrontendData('runtimeManifest', $runtimeResolution->runtimeManifest);
        $context->setFrontendData('publicPageRenderData', $renderContext->publicRenderData);
        $context->setFrontendData('assetManifest', $renderContext->publicRenderData->assetManifest);
        $context->setFrontendData('mediaHints', $renderContext->publicRenderData->mediaHints);
        $context->setFrontendData('lcpMediaUrl', $renderContext->publicRenderData->mediaHints[0]->url ?? null);
        $context->setFrontendData(
            'performanceReport',
            BuildPublicRenderPerformanceReportAction::run($renderContext->publicRenderData, $renderContext),
        );

        return $renderer->render($renderContext);
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    private function withWorkspaceContext(PagePreviewSnapshotData $snapshot, callable $callback): mixed
    {
        if (! class_exists('Capell\\PublishingStudio\\WorkspaceContext')) {
            return $callback();
        }

        $workspace = null;

        if ($snapshot->workspaceId !== null && class_exists('Capell\\PublishingStudio\\Models\\Workspace')) {
            $workspace = Workspace::query()->find($snapshot->workspaceId);
        }

        return WorkspaceContext::runWith($workspace, $callback);
    }
}

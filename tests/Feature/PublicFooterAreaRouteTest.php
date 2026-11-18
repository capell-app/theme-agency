<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\LayoutBuilder\Support\CapellLayoutManager;

use function Pest\Laravel\get;

require_once dirname(__DIR__, 4) . '/tests/Packages/Support/PublicOutputSafety.php';
require_once dirname(__DIR__, 4) . '/tests/Packages/Support/ThemeLayoutNativeSupport.php';

it('renders a populated footer-area widget on the public route with fallback chrome disabled', function (): void {
    $registry = resolve(ThemeRegistry::class);
    $registry->reset();

    // The helper seeds the theme demo and disables the shared header and footer chrome.
    [$pageUrl] = layoutNativeThemeCreatePage('agency', 'Public Footer Area');

    $homepage = Page::query()
        ->where('meta->theme_demo->theme_key', 'agency')
        ->where('meta->theme_demo->surface', 'homepage')
        ->firstOrFail();
    $marker = 'Footer area widget marker 7f3a91c2';
    $homepage->translations()->update(['content' => '<p>' . $marker . '</p>']);
    $layout = $homepage->loadMissing('layout')->layout;

    throw_unless($layout instanceof Layout, RuntimeException::class, 'Expected the demo homepage to have a layout.');

    $containers = is_array($layout->containers) ? $layout->containers : [];
    $main = is_array($containers['main'] ?? null) ? $containers['main'] : ['widgets' => []];
    $main['widgets'] = array_values(array_filter(
        is_array($main['widgets'] ?? null) ? $main['widgets'] : [],
        static fn (mixed $widget): bool => ! is_array($widget) || ($widget['widget_key'] ?? null) !== 'page-content',
    ));
    $containers['main'] = $main;
    $containers['internal-campaign-42'] = [
        'meta' => ['area' => 'footer', 'colspan' => 12],
        'widgets' => [['widget_key' => 'page-content', 'occurrence' => 1]],
    ];
    $layoutMeta = is_array($layout->meta) ? $layout->meta : [];
    unset($layoutMeta['footer']);
    $layout->forceFill(['containers' => $containers, 'meta' => $layoutMeta])->save();

    CapellLayoutManager::clearContainerWidgets();

    // Anonymous request: no actingAs, so the response is what a visitor receives.
    $response = get($pageUrl->full_url)->assertOk();
    $html = (string) $response->getContent();

    // The page-content widget now lives only in the footer area, so its unique marker must
    // appear after the footer opens and nowhere before it.
    $footerOffset = strrpos($html, '<footer');
    expect($footerOffset)->not->toBeFalse();
    $footerHtml = substr($html, (int) $footerOffset);

    expect($footerHtml)->toContain($marker);
    expect(substr($html, 0, (int) $footerOffset))->not->toContain($marker);
    expect($html)->not->toContain('internal-campaign-42');

    assertCapellPublicOutputIsSafe($response, 'agency footer-area public HTML');

    CapellLayoutManager::clearContainerWidgets();
    CapellCore::clearPackages();
    $registry->reset();
});

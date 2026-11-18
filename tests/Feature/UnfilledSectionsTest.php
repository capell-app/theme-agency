<?php

declare(strict_types=1);

use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\Theme;
use Capell\Core\Models\Translation;
use Capell\Frontend\Contracts\FrontendContextReader;
use Capell\Frontend\Data\FrontendContext;
use Capell\Frontend\Facades\Frontend;
use Capell\LayoutBuilder\Enums\WidgetComponentEnum;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Support\CapellLayoutManager;
use Capell\Tests\Support\UnfilledThemeSections;
use Dom\HTMLDocument;
use Illuminate\Support\Facades\View;

/*
 * A section an owner places but does not fill must not publish invented
 * portfolios, figures, people, awards or dead links, and discipline labels
 * must not pretend to filter another section. Content sections render nothing
 * until they carry the owner's own content; chrome sections render only the
 * parts the owner supplied.
 */
beforeEach(function (): void {
    View::addNamespace('capell-theme-agency', dirname(__DIR__, 2) . '/resources/views');
});

/**
 * @param  array<string, mixed>  $section
 */
function agencySectionHtml(string $view, array $section): string
{
    /** @var view-string $viewName */
    $viewName = sprintf('capell-theme-agency::sections.%s', $view);

    return view($viewName, ['section' => $section])->render();
}

/**
 * @param  array<string, mixed>  $section
 */
function agencySectionDocument(string $view, array $section): HTMLDocument
{
    return HTMLDocument::createFromString(agencySectionHtml($view, $section), LIBXML_NOERROR);
}

it('renders nothing for a content section placed without content', function (string $view): void {
    expect(trim(agencySectionHtml($view, [])))->toBe('');
})->with([
    'hero',
    'cta',
    'proof',
    'awarded-profiles',
    'awarded-profiles--spotlight',
    'content-listing',
    'creator-directory',
    'education-upsell',
    'education-upsell--cta',
    'featured-portfolios',
    'featured-portfolios--parallax',
    'filter-taxonomies',
    'filter-taxonomies--grid',
    'portfolio-grid',
    'portfolio-grid--gallery-wall',
    'navigation',
    'footer',
]);

it('renders nothing for an item section whose items are empty, even with a heading', function (string $view): void {
    expect(trim(agencySectionHtml($view, ['heading' => 'Owner heading', 'items' => []])))->toBe('');
})->with([
    'proof',
    'awarded-profiles',
    'awarded-profiles--spotlight',
    'creator-directory',
    'education-upsell',
    'education-upsell--cta',
    'featured-portfolios',
    'featured-portfolios--parallax',
    'filter-taxonomies',
    'filter-taxonomies--grid',
    'portfolio-grid',
    'portfolio-grid--gallery-wall',
]);

it('renders hero buttons only for actions with a label and a safe URL', function (): void {
    $document = agencySectionDocument('hero', [
        'heading' => 'Owner headline',
        'actions' => [
            ['label' => 'Has a link', 'url' => '/work'],
            ['label' => 'No link'],
            ['label' => 'Unsafe link', 'url' => 'javascript:alert(1)'],
        ],
    ]);
    $buttons = $document->querySelectorAll('.ppc-actions a');

    expect($buttons)->toHaveCount(1)
        ->and($buttons->item(0)?->getAttribute('href'))->toBe('/work');
});

it('renders no hero actions or panel when the owner supplies only a heading', function (): void {
    $document = agencySectionDocument('hero', ['heading' => 'Owner headline']);

    expect($document->querySelector('h1')?->textContent)->toContain('Owner headline')
        ->and($document->querySelectorAll('a'))->toHaveCount(0)
        ->and($document->querySelector('.ppc-kicker'))->toBeNull()
        ->and($document->querySelector('.ppc-badge'))->toBeNull()
        ->and($document->querySelector('aside'))->toBeNull();
});

it('keeps flat primary and secondary hero actions, but only with a URL', function (): void {
    $document = agencySectionDocument('hero', [
        'heading' => 'Owner headline',
        'primary_label' => 'Browse',
        'primary_url' => '/work',
        'secondary_label' => 'No link',
    ]);
    $buttons = $document->querySelectorAll('.ppc-actions a');

    expect($buttons)->toHaveCount(1)
        ->and($buttons->item(0)?->getAttribute('href'))->toBe('/work');
});

it('renders hero notes and badge only when the owner supplies them', function (): void {
    $document = agencySectionDocument('hero', [
        'heading' => 'Owner headline',
        'badge' => 'Owner badge',
        'notes' => ['Owner note'],
    ]);

    expect($document->querySelectorAll('.ppc-badge'))->toHaveCount(1)
        ->and($document->querySelectorAll('aside .ppc-meta'))->toHaveCount(1);
});

it('keeps a cta without a safe URL from rendering a dead button', function (): void {
    $document = agencySectionDocument('cta', ['heading' => 'Owner call to action', 'label' => 'Submit']);

    expect($document->querySelector('h2')?->textContent)->toContain('Owner call to action')
        ->and($document->querySelectorAll('a'))->toHaveCount(0);

    $linked = agencySectionDocument('cta', ['heading' => 'Owner call to action', 'label' => 'Submit', 'url' => '/submit']);

    expect($linked->querySelectorAll('a'))->toHaveCount(1)
        ->and($linked->querySelector('a')?->getAttribute('href'))->toBe('/submit');
});

it('renders proof figures only from owner items', function (): void {
    $document = agencySectionDocument('proof', ['items' => [['value' => '12', 'label' => 'Owner figure']]]);

    expect($document->querySelectorAll('.ppc-card'))->toHaveCount(1)
        ->and($document->querySelector('.ppc-kicker'))->toBeNull()
        ->and($document->querySelector('h2'))->toBeNull();
});

it('renders filter labels as plain text unless the item has its own URL', function (string $view): void {
    $html = agencySectionHtml($view, [
        'items' => [
            ['title' => 'Branding'],
            ['title' => 'Motion', 'url' => '/disciplines/motion'],
        ],
    ]);
    $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
    $links = $document->querySelectorAll('a');

    expect($document->querySelectorAll('.ppc-filters .ppc-filter-chip'))->toHaveCount(2)
        ->and($links)->toHaveCount(1)
        ->and($links->item(0)?->getAttribute('href'))->toBe('/disciplines/motion')
        ->and($html)->not->toContain('href="#');
})->with(['filter-taxonomies', 'filter-taxonomies--grid']);

it('renders an award badge only for profiles that carry an award', function (): void {
    $document = agencySectionDocument('awarded-profiles', [
        'items' => [
            ['title' => 'Owner winner', 'award' => 'Owner award'],
            ['title' => 'Owner entry without award'],
        ],
    ]);

    expect($document->querySelectorAll('article'))->toHaveCount(2)
        ->and($document->querySelectorAll('.ppc-badge'))->toHaveCount(1)
        ->and($document->querySelector('.ppc-badge')?->textContent)->toContain('Owner award');
});

it('prints a spotlight badge only for a saved tier or award', function (): void {
    $document = agencySectionDocument('awarded-profiles--spotlight', [
        'items' => [
            ['title' => 'Owner winner', 'tier' => 'gold', 'award' => 'Owner award'],
            ['title' => 'Owner runner-up', 'award' => 'Owner second award'],
            ['title' => 'Owner entry without tier or award'],
        ],
    ]);
    $cards = $document->querySelectorAll('.ppc-spotlight-card');

    expect($cards)->toHaveCount(3)
        ->and($cards->item(0)?->querySelector('.ppc-badge')?->textContent)->toContain('·', 'Owner award')
        ->and($cards->item(1)?->querySelector('.ppc-badge')?->textContent)->not->toContain('·')
        ->and($cards->item(2)?->querySelector('.ppc-badge'))->toBeNull();
});

it('renders the awarded-profiles button only with a label and a safe URL', function (string $view): void {
    $items = [['title' => 'Owner entry']];

    expect(agencySectionDocument($view, ['items' => $items, 'label' => 'See all'])->querySelectorAll('.ppc-button'))->toHaveCount(0)
        ->and(agencySectionDocument($view, ['items' => $items, 'url' => '/awards'])->querySelectorAll('.ppc-button'))->toHaveCount(0);

    $document = agencySectionDocument($view, ['items' => $items, 'label' => 'See all', 'url' => '/awards']);

    expect($document->querySelector('a.ppc-button')?->getAttribute('href'))->toBe('/awards');
})->with(['awarded-profiles', 'awarded-profiles--spotlight']);

it('renders item cards as plain text without a URL and links only with a safe one', function (string $view): void {
    $document = agencySectionDocument($view, [
        'items' => [
            ['title' => 'Unlinked entry', 'summary' => 'Owner summary'],
            ['title' => 'Linked entry', 'url' => '/work/linked'],
            ['title' => 'Unsafe entry', 'url' => 'javascript:alert(1)'],
        ],
    ]);
    $links = $document->querySelectorAll('h3 a');

    expect($document->querySelectorAll('article'))->toHaveCount(3)
        ->and($links)->toHaveCount(1)
        ->and($links->item(0)?->getAttribute('href'))->toBe('/work/linked');
})->with([
    'featured-portfolios',
    'featured-portfolios--parallax',
    'portfolio-grid',
    'portfolio-grid--gallery-wall',
    'creator-directory',
    'education-upsell',
]);

it('prints no invented meta chip or note on portfolio cards', function (string $view): void {
    $document = agencySectionDocument($view, ['items' => [['title' => 'Owner entry']]]);

    expect($document->querySelectorAll('article'))->toHaveCount(1)
        ->and($document->querySelector('.ppc-chip'))->toBeNull()
        ->and($document->querySelector('.ppc-card-meta-row'))->toBeNull();

    $supplied = agencySectionDocument($view, ['items' => [['title' => 'Owner entry', 'meta' => 'Owner meta', 'care_note' => 'Owner note']]]);

    expect($supplied->querySelector('.ppc-chip')?->textContent)->toContain('Owner meta')
        ->and($supplied->querySelector('.ppc-card-meta-row .ppc-meta')?->textContent)->toContain('Owner note');
})->with(['portfolio-grid', 'portfolio-grid--gallery-wall']);

it('keeps the education lead banner button to a lead item with a label and URL', function (): void {
    $bare = agencySectionDocument('education-upsell--cta', ['items' => [['title' => 'Owner course']]]);

    expect($bare->querySelectorAll('.ppc-course-lead'))->toHaveCount(1)
        ->and($bare->querySelectorAll('.ppc-course-lead .ppc-chip'))->toHaveCount(0)
        ->and($bare->querySelectorAll('a'))->toHaveCount(0);

    $linked = agencySectionDocument('education-upsell--cta', [
        'lead_label' => 'Owner lead label',
        'cta_label' => 'Read on',
        'items' => [['title' => 'Owner course', 'url' => '/learn/course']],
    ]);

    expect($linked->querySelectorAll('.ppc-course-lead .ppc-chip'))->toHaveCount(1)
        ->and($linked->querySelectorAll('a.ppc-button'))->toHaveCount(1)
        ->and($linked->querySelector('a.ppc-button')?->getAttribute('href'))->toBe('/learn/course');
});

it('renders the content listing heading only when supplied and an empty state without invented copy', function (): void {
    $document = agencySectionDocument('content-listing', ['heading' => 'Owner archive']);

    expect($document->querySelector('h2')?->textContent)->toContain('Owner archive')
        ->and($document->querySelector('.ppc-kicker'))->toBeNull()
        ->and($document->querySelectorAll('article'))->toHaveCount(0)
        ->and($document->querySelector('.ppc-lede'))->not->toBeNull();

    $items = agencySectionDocument('content-listing', ['items' => [['title' => 'Owner entry']]]);

    expect($items->querySelector('h2'))->toBeNull()
        ->and($items->querySelector('.ppc-lede'))->toBeNull()
        ->and($items->querySelectorAll('article'))->toHaveCount(1);
});

it('renders navigation without invented links, brand or tagline', function (): void {
    expect(trim(agencySectionHtml('navigation', [])))->toBe('');

    expect(agencySectionDocument('navigation', ['brandName' => 'Owner brand name'])->querySelector('.ppc-masthead-wordmark')?->textContent)->toContain('Owner brand name');

    $branded = agencySectionDocument('navigation', ['brand' => 'Owner studio']);

    expect($branded->querySelector('.ppc-masthead-wordmark')?->textContent)->toContain('Owner studio')
        ->and($branded->querySelector('.ppc-masthead-tagline'))->toBeNull()
        ->and($branded->querySelector('ul'))->toBeNull();
});

it('renders navigation links only when they have a label and a safe URL', function (): void {
    $document = agencySectionDocument('navigation', [
        'items' => [
            ['label' => 'Work', 'url' => '/work'],
            ['label' => 'No link'],
            ['label' => 'Unsafe', 'url' => 'javascript:alert(1)'],
        ],
        'ctaLabel' => 'Submit',
    ]);
    $links = $document->querySelectorAll('ul.ppc-masthead-nav a');

    expect($links->item(0)?->getAttribute('href'))->toBe('/work')
        ->and($document->querySelectorAll('ul.ppc-masthead-nav a.ppc-masthead-cta'))->toHaveCount(0);

    $withCta = agencySectionDocument('navigation', ['ctaLabel' => 'Submit', 'ctaUrl' => '/submit']);

    expect($withCta->querySelector('a.ppc-masthead-cta')?->getAttribute('href'))->toBe('/submit');
});

it('renders no footer at all when the owner supplies no brand, summary or columns', function (): void {
    expect(trim(agencySectionHtml('footer', [])))->toBe('')
        ->and(trim(agencySectionHtml('footer', ['columns' => [['title' => 'Empty column', 'links' => []]]])))->toBe('');
});

it('renders footer columns without invented links and without an empty heading', function (): void {
    $document = agencySectionDocument('footer', [
        'brand' => 'Owner studio',
        'columns' => [
            ['links' => [['label' => 'Linked', 'url' => '/linked'], ['label' => 'Plain']]],
            ['title' => 'Empty column'],
        ],
    ]);

    expect($document->querySelector('.ppc-footer-wordmark')?->textContent)->toContain('Owner studio')
        ->and($document->querySelectorAll('.ppc-footer-heading'))->toHaveCount(0)
        ->and($document->querySelectorAll('.ppc-footer-links'))->toHaveCount(1)
        ->and($document->querySelectorAll('.ppc-footer-links li'))->toHaveCount(2)
        ->and($document->querySelectorAll('.ppc-footer-links a'))->toHaveCount(1);
});

it('keeps a working newsletter form without a heading, and prints the heading only when supplied', function (): void {
    $bare = agencySectionDocument('newsletter', ['button' => 'Owner subscribe']);

    expect($bare->querySelector('section#newsletter input[type="email"][required]'))->not->toBeNull()
        ->and($bare->querySelector('section#newsletter button[type="submit"]')?->textContent)->toContain('Owner subscribe')
        ->and($bare->querySelector('h2'))->toBeNull()
        ->and($bare->querySelector('.ppc-kicker'))->toBeNull()
        ->and($bare->querySelector('.ppc-lede'))->toBeNull();

    expect(agencySectionDocument('newsletter', ['heading' => 'Owner newsletter'])->querySelector('h2')?->textContent)->toContain('Owner newsletter');
});

it('keeps the contact form section without a heading, and prints the heading only when supplied', function (): void {
    $bare = agencySectionDocument('form', []);

    expect($bare->querySelector('section#contact-form'))->not->toBeNull()
        ->and($bare->querySelector('h2'))->toBeNull()
        ->and($bare->querySelector('.ppc-kicker'))->toBeNull();

    expect(agencySectionDocument('form', ['heading' => 'Owner form'])->querySelector('h2')?->textContent)->toContain('Owner form');
});

it('prints a section kicker only when the owner supplies one', function (string $view): void {
    $items = [['title' => 'Owner entry']];

    expect(agencySectionDocument($view, ['items' => $items])->querySelector('.ppc-kicker'))->toBeNull()
        ->and(agencySectionDocument($view, ['items' => $items, 'kicker' => 'Owner kicker'])->querySelector('.ppc-kicker')?->textContent)->toContain('Owner kicker');
})->with([
    'featured-portfolios',
    'featured-portfolios--parallax',
    'filter-taxonomies',
    'filter-taxonomies--grid',
    'portfolio-grid',
    'portfolio-grid--gallery-wall',
]);

/**
 * @param  array<string, mixed>  $containers
 */
function agencyBindLayout(array $containers): void
{
    app()->instance(FrontendContextReader::class, new FrontendContext(null, null, new Page, new Layout(['containers' => $containers]), new Theme(['key' => 'default', 'meta' => []]), [], null));
    Frontend::clearResolvedInstance(FrontendContextReader::class);
}

it('renders configured footer-area widgets even when the shared footer chrome is disabled', function (): void {
    CapellLayoutManager::clearContainerWidgets();
    CapellLayoutManager::storeContainerWidget('footer-area', 'footer-note', (new Widget)->forceFill([
        'key' => 'footer-note',
        'name' => 'Footer note',
        'component' => WidgetComponentEnum::Default->value,
        'is_livewire' => false,
        'meta' => [],
    ])->setRelation('translation', new Translation(['title' => 'Owner footer widget']))->setRelation('blueprint', new Blueprint));
    agencyBindLayout(['footer-area' => ['meta' => ['area' => 'footer', 'colspan' => 12], 'widgets' => [['widget_key' => 'footer-note', 'occurrence' => 1]]]]);

    $html = agencySectionHtml('footer', ['type' => 'footer']);
    $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);

    expect($document->querySelectorAll('footer'))->toHaveCount(1)
        ->and($document->querySelectorAll('footer .layout-container'))->toHaveCount(1)
        ->and($document->querySelector('footer')?->textContent)->toContain('Owner footer widget')
        ->and($document->querySelector('.ppc-footer-grid'))->toBeNull();

    CapellLayoutManager::clearContainerWidgets();
    agencyBindLayout([]);
});

it('renders no footer when the layout has no populated footer area and the section has no content', function (): void {
    agencyBindLayout(['main' => ['meta' => ['area' => 'main'], 'widgets' => [['widget_key' => 'x', 'occurrence' => 1]]], 'footer-area' => ['meta' => ['area' => 'footer'], 'widgets' => []]]);

    expect(trim(agencySectionHtml('footer', ['type' => 'footer'])))->toBe('');

    agencyBindLayout([]);
});

it('keeps a valid cta action when the heading is blank', function (): void {
    $document = agencySectionDocument('cta', ['actions' => [['label' => 'Owner action', 'url' => '/go']]]);

    expect($document->querySelector('h2'))->toBeNull()
        ->and($document->querySelectorAll('.ppc-actions a'))->toHaveCount(1)
        ->and($document->querySelector('.ppc-actions a')?->getAttribute('href'))->toBe('/go');

    $flat = agencySectionDocument('cta', ['label' => 'Flat action', 'url' => '/flat']);

    expect($flat->querySelector('a')?->getAttribute('href'))->toBe('/flat')
        ->and(trim(agencySectionHtml('cta', ['actions' => [['label' => 'No link']]])))->toBe('');
});

it('keeps a media-only hero without inventing a heading', function (): void {
    $document = agencySectionDocument('hero', ['mediaUrl' => '/media/hero.jpg', 'mediaAlt' => 'Owner caption']);

    expect($document->querySelectorAll('section'))->toHaveCount(1)
        ->and($document->querySelectorAll('h1'))->toHaveCount(0)
        ->and($document->querySelectorAll('.ppc-plate-media'))->toHaveCount(1);
});

it('keeps an action-only hero without inventing a heading', function (): void {
    $document = agencySectionDocument('hero', ['actions' => [['label' => 'Owner action', 'url' => '/go'], ['label' => 'No link']]]);

    expect($document->querySelectorAll('section'))->toHaveCount(1)
        ->and($document->querySelectorAll('h1'))->toHaveCount(0)
        ->and($document->querySelectorAll('.ppc-actions a'))->toHaveCount(1)
        ->and($document->querySelector('.ppc-actions a')?->getAttribute('href'))->toBe('/go');
});

$unfilled = UnfilledThemeSections::for(dirname(__DIR__, 2));

it('publishes nothing for a content section placed without content', function (string $view) use ($unfilled): void {
    expect($unfilled->published($view))->toBe(['text' => '', 'links' => [], 'media' => 0, 'controls' => 0]);
})->with($unfilled->contentViews());

it('publishes no placeholder or demo-page link, and no functional-section link or media, when unfilled', function (string $view) use ($unfilled): void {
    expect($unfilled->violations($view))->toBe([]);
})->with($unfilled->views());

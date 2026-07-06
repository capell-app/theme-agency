<?php

declare(strict_types=1);

/*
 * Wave 4a render coverage: every signature widget introduced for far-field's
 * "geographic + audio narrative" headline mechanic renders without error,
 * across every declared variant, given a minimal payload -- and the caps
 * from §0.3 (carousels <= 20, grids <= 50, timelines <= 50) are respected by
 * construction (the Blade views themselves ->take() the payload).
 */

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

beforeEach(function (): void {
    View::addNamespace('capell-theme-far-field', dirname(__DIR__, 2) . '/resources/views');
    Lang::addNamespace('capell-theme-far-field', dirname(__DIR__, 2) . '/resources/lang');
});

it('renders every declared radio-audio (audio-embedded-with-transcript) variant', function (string $view): void {
    $section = [
        'heading' => 'Audio that feels part of the issue',
        'summary' => 'Interviews and field recordings from our correspondents.',
        'audio_url' => '/media/morning-briefing.mp3',
        'audio_title' => 'Morning briefing',
        'transcript' => [
            ['start' => 0, 'end' => 10, 'speaker' => 'Host', 'text' => 'Good morning.'],
            ['start' => 10, 'end' => 20, 'speaker' => 'Correspondent', 'text' => 'Reporting from Tbilisi.'],
        ],
        'items' => [
            ['title' => 'Morning briefing', 'summary' => 'The day in world affairs.'],
        ],
    ];

    $html = view($view, ['section' => $section])->render();

    expect($html)
        ->toContain('Audio that feels part of the issue')
        ->toContain('<audio')
        ->toContain('data-audio-transcript-widget')
        ->toContain('Good morning.')
        ->toContain('timeupdate');
})->with([
    'base' => ['capell-theme-far-field::sections.radio-audio'],
    'immersive' => ['capell-theme-far-field::sections.radio-audio--immersive'],
]);

it('renders the radio-audio widget as a readable transcript with no audio URL', function (): void {
    $section = [
        'heading' => 'Audio that feels part of the issue',
        'transcript' => [
            ['start' => 0, 'end' => 10, 'speaker' => 'Host', 'text' => 'Good morning.'],
        ],
    ];

    $html = view('capell-theme-far-field::sections.radio-audio', ['section' => $section])->render();

    expect($html)
        ->not->toContain('<audio')
        ->toContain('Audio that feels part of the issue');
});

it('renders every declared city-guides (destination-atlas) variant', function (string $view): void {
    $section = [
        'heading' => 'Practical guides with editorial taste',
        'items' => [
            ['title' => 'Tokyo', 'summary' => 'Retail, restaurants, and transport.', 'x' => 84, 'y' => 38, 'url' => '/tokyo'],
            ['title' => 'Lisbon', 'summary' => 'Hotels, culture, and viewpoints.', 'x' => 38, 'y' => 34, 'url' => '/lisbon'],
        ],
    ];

    $html = view($view, ['section' => $section])->render();

    expect($html)
        ->toContain('Practical guides with editorial taste')
        ->toContain('<svg')
        ->toContain('gcm-atlas-pin')
        ->toContain('Tokyo')
        ->toContain('Lisbon');
})->with([
    'base' => ['capell-theme-far-field::sections.city-guides'],
    'tabs' => ['capell-theme-far-field::sections.city-guides--tabs'],
]);

it('renders the destination-atlas tabs variant with a role=tablist pattern', function (): void {
    $html = view('capell-theme-far-field::sections.city-guides--tabs', ['section' => []])->render();

    expect($html)
        ->toContain('role="tablist"')
        ->toContain('role="tab"')
        ->toContain('role="tabpanel"')
        ->toContain('aria-controls');
});

it('caps destination-atlas at 8 destinations regardless of payload size', function (): void {
    $items = [];

    foreach (range(1, 20) as $index) {
        $items[] = ['title' => "City {$index}", 'summary' => 'A guide.', 'x' => 10, 'y' => 10];
    }

    $html = view('capell-theme-far-field::sections.city-guides', ['section' => ['items' => $items]])->render();

    expect(substr_count($html, 'gcm-atlas-pin'))->toBeLessThanOrEqual(8);
});

it('renders every declared photo-essay (photo-essay-with-lazy-captions) variant', function (string $view): void {
    $section = [
        'heading' => 'A story told frame by frame',
        'items' => [
            ['image' => '/media/frame-1.jpg', 'caption' => 'Dawn over the harbour.', 'meta' => 'Frame 1'],
            ['image' => '/media/frame-2.jpg', 'caption' => 'The market rebuilds itself daily.', 'meta' => 'Frame 2'],
        ],
    ];

    $html = view($view, ['section' => $section])->render();

    expect($html)
        ->toContain('A story told frame by frame')
        ->toContain('loading="eager"')
        ->toContain('loading="lazy"')
        ->toContain('Dawn over the harbour.');
})->with([
    'base' => ['capell-theme-far-field::sections.photo-essay'],
    'reveal' => ['capell-theme-far-field::sections.photo-essay--reveal'],
]);

it('renders every declared cultural-dispatch-timeline variant', function (string $view): void {
    $section = [
        'heading' => 'The year in dispatches',
        'items' => [
            ['date' => '2026-01-06', 'meta' => 'Affairs', 'title' => 'The first dispatch', 'summary' => 'An opening report.', 'url' => '/dispatch-one'],
            ['date' => '2026-02-14', 'meta' => 'Culture', 'title' => 'The biennale opens', 'summary' => 'A packed harbour.', 'url' => '/dispatch-two'],
        ],
    ];

    $html = view($view, ['section' => $section])->render();

    expect($html)
        ->toContain('The year in dispatches')
        ->toContain('The first dispatch')
        ->toContain('datetime="2026-01-06"')
        ->toContain('gcm-dispatch-timeline');
})->with([
    'base' => ['capell-theme-far-field::sections.cultural-dispatch-timeline'],
    'compact' => ['capell-theme-far-field::sections.cultural-dispatch-timeline--compact'],
]);

it('caps cultural-dispatch-timeline at 50 entries regardless of payload size', function (): void {
    $items = [];

    foreach (range(1, 60) as $index) {
        $items[] = ['date' => '2026-01-01', 'meta' => 'Affairs', 'title' => "Entry {$index}", 'summary' => 'A dispatch.'];
    }

    $html = view('capell-theme-far-field::sections.cultural-dispatch-timeline', ['section' => ['items' => $items]])->render();

    expect(substr_count($html, 'gcm-dispatch-timeline-entry '))->toBeLessThanOrEqual(50);
});

it('renders every declared columnists (columnists-with-latest-essay-preview) variant', function (string $view): void {
    $section = [
        'heading' => 'The regular voices readers come back to',
        'items' => [
            [
                'title' => 'The editor letter',
                'summary' => 'The week in affairs, culture, and commerce.',
                'latest_title' => 'What the quiet quarter tells us',
                'latest_date' => '2026-06-29',
                'url' => '/columnist-editor',
            ],
        ],
    ];

    $html = view($view, ['section' => $section])->render();

    expect($html)
        ->toContain('The regular voices readers come back to')
        ->toContain('Latest essay')
        ->toContain('What the quiet quarter tells us')
        ->toContain('datetime="2026-06-29"');
})->with([
    'base' => ['capell-theme-far-field::sections.columnists'],
    'roster' => ['capell-theme-far-field::sections.columnists--roster'],
]);

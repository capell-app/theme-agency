<?php

declare(strict_types=1);

use Capell\FrontendOptimizer\Actions\BuildOptimizedImageMarkupAction;

it('renders responsive picture markup with lazy loading and modern formats', function (): void {
    $html = (string) BuildOptimizedImageMarkupAction::run(
        src: '/storage/media/hero.jpg',
        alt: 'Hero image',
        width: 1280,
        height: 720,
        sizes: '(min-width: 1024px) 960px, 100vw',
        widths: [320, 640],
        attributes: ['class' => 'hero-image'],
    );

    expect($html)->toContain('<picture>')
        ->and($html)->toContain('type="image/avif"')
        ->and($html)->toContain('/storage/media/hero.jpg?w=320&amp;format=avif&amp;q=82 320w')
        ->and($html)->toContain('type="image/webp"')
        ->and($html)->toContain('src="/storage/media/hero.jpg"')
        ->and($html)->toContain('srcset="/storage/media/hero.jpg?w=320&amp;q=82 320w')
        ->and($html)->toContain('sizes="(min-width: 1024px) 960px, 100vw"')
        ->and($html)->toContain('alt="Hero image"')
        ->and($html)->toContain('width="1280"')
        ->and($html)->toContain('height="720"')
        ->and($html)->toContain('loading="lazy"')
        ->and($html)->toContain('decoding="async"')
        ->and($html)->toContain('class="hero-image"');
});

it('preserves existing query strings and marks eager images as high priority', function (): void {
    $html = (string) BuildOptimizedImageMarkupAction::run(
        src: 'https://cdn.example.test/image.png?crop=center#intro',
        alt: 'Priority image',
        widths: [800],
        eager: true,
    );

    expect($html)->toContain('https://cdn.example.test/image.png?crop=center&amp;w=800&amp;format=avif&amp;q=82#intro 800w')
        ->and($html)->toContain('loading="eager"')
        ->and($html)->toContain('fetchpriority="high"');
});

it('falls back to an img tag for unsupported formats', function (): void {
    $html = (string) BuildOptimizedImageMarkupAction::run(
        src: '/storage/media/logo.svg',
        alt: 'Logo',
        widths: [320],
    );

    expect($html)->not->toContain('<picture>')
        ->and($html)->toContain('<img ')
        ->and($html)->toContain('loading="lazy"');
});

it('does not render unsafe image sources', function (): void {
    expect((string) BuildOptimizedImageMarkupAction::run('javascript:alert(1)', 'Unsafe'))->toBe('');
});

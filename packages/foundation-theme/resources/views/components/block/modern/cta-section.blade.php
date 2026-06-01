@props([
    'title' => $block->translation?->title,
    'content' => $block->translation?->content,
    'primaryButtonText' => $block->getMeta('primary_button_text'),
    'primaryButtonUrl' => $block->getMeta('primary_button_url', '#'),
    'secondaryButtonText' => $block->getMeta('secondary_button_text'),
    'secondaryButtonUrl' => $block->getMeta('secondary_button_url', '#'),
    'container',
    'containerKey',
    'containerWidth' => null,
    'loop',
    'block',
])

<x-capell-foundation-theme::block.wrapper
    class="capell-modern-cta-section block-ap-cta-section"
    :$container
    :$containerKey
    :$containerWidth
    :index="$loop->index"
    :$block
>
    <section class="ap-showcase-cta capell-showcase">
        <div class="ap-showcase-cta__panel capell-showcase__inner">
            <div class="capell-showcase__section-head mb-0">
                @if ($title)
                    <h2 class="ap-cta-headline capell-showcase__heading">
                        {{ $title }}
                    </h2>
                @endif

                @if ($content)
                    <p class="ap-cta-description capell-showcase__copy">
                        {!! strip_tags($content) !!}
                    </p>
                @endif

                @if ($primaryButtonText || $secondaryButtonText)
                    <div class="flex flex-wrap gap-3 pt-2">
                        @if ($primaryButtonText)
                            <a
                                href="{{ $primaryButtonUrl }}"
                                class="ap-showcase-button ap-showcase-button--primary ap-cta-primary-btn"
                            >
                                <span>{{ $primaryButtonText }}</span>
                                @svg('heroicon-o-arrow-right', 'h-4 w-4')
                            </a>
                        @endif

                        @if ($secondaryButtonText)
                            <a
                                href="{{ $secondaryButtonUrl }}"
                                class="ap-showcase-button ap-showcase-button--secondary ap-cta-secondary-btn"
                            >
                                <span>{{ $secondaryButtonText }}</span>
                                @svg('heroicon-o-code-bracket-square', 'h-4 w-4')
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <div
                class="ap-showcase-cta__proof"
                aria-label="Capell demo proof"
            >
                <div class="ap-showcase-cta__proof-row">
                    <span class="ap-showcase-cta__proof-mark">
                        @svg('heroicon-o-check', 'h-4 w-4')
                    </span>
                    <p class="ap-showcase-cta__proof-text">
                        Homepage content is block, media, and layout driven.
                    </p>
                </div>

                <div class="ap-showcase-cta__proof-row">
                    <span class="ap-showcase-cta__proof-mark">
                        @svg('heroicon-o-check', 'h-4 w-4')
                    </span>
                    <p class="ap-showcase-cta__proof-text">
                        Runtime assets are package-owned and doctor verified.
                    </p>
                </div>

                <div class="ap-showcase-cta__proof-row">
                    <span class="ap-showcase-cta__proof-mark">
                        @svg('heroicon-o-check', 'h-4 w-4')
                    </span>
                    <p class="ap-showcase-cta__proof-text">
                        Built for Laravel, Filament, and serious CMS teams.
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-capell-foundation-theme::block.wrapper>

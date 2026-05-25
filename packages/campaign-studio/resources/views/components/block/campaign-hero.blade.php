@props([
    'title' => $block->translation?->title,
    'content' => $block->translation?->content,
    'eyebrow' => $block->getMeta('eyebrow'),
    'primaryButtonText' => $block->getMeta('primary_button_text'),
    'primaryButtonUrl' => $block->getMeta('primary_button_url', '#'),
    'secondaryButtonText' => $block->getMeta('secondary_button_text'),
    'secondaryButtonUrl' => $block->getMeta('secondary_button_url', '#'),
    'goalKey' => $block->getMeta('goal_key'),
    'container',
    'containerKey',
    'containerWidth' => null,
    'loop',
    'block',
])

<x-capell-foundation-theme::block.wrapper
    class="capell-block-campaign-hero block-campaign-hero"
    :$container
    :$containerKey
    :$containerWidth
    :index="$loop->index"
    :$block
>
    <section class="campaign-hero px-6 py-16">
        <div class="mx-auto max-w-5xl">
            @if ($eyebrow)
                <p class="mb-3 text-sm font-semibold tracking-wide uppercase">
                    {{ $eyebrow }}
                </p>
            @endif

            @if ($title)
                <h1 class="max-w-3xl text-4xl font-bold">{{ $title }}</h1>
            @endif

            @if ($content)
                <div class="mt-5 max-w-2xl text-lg">{!! $content !!}</div>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                @if ($primaryButtonText)
                    <a
                        href="{{ $primaryButtonUrl }}"
                        class="layout-builder-btn layout-builder-btn-primary"
                        data-campaign-goal="{{ $goalKey }}"
                        data-campaign-location="hero-primary"
                    >
                        {{ $primaryButtonText }}
                    </a>
                @endif

                @if ($secondaryButtonText)
                    <a
                        href="{{ $secondaryButtonUrl }}"
                        class="layout-builder-btn layout-builder-btn-secondary"
                        data-campaign-location="hero-secondary"
                    >
                        {{ $secondaryButtonText }}
                    </a>
                @endif
            </div>
        </div>
    </section>
</x-capell-foundation-theme::block.wrapper>

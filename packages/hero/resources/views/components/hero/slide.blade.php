@php
    use Capell\Frontend\Facades\Frontend;
@endphp

@props ([
    'backgroundAttachment' => '',
    'backgroundColor' => '',
    'backgroundImage' => null,
    'backgroundOverlay' => null,
    'backgroundPosition' => 'center',
    'backgroundRepeat' => 'no-repeat',
    'backgroundSize' => 'cover',
    'color' => 'dark',
    'containerClass' => '',
    'first' => false,
    'height' => '',
    'heroBackground' => null,
    'backgroundKey' => null,
    'heroMedia' => null,
    'slideBgImgClass' => '',
    'title' => null,
])
{{-- format-ignore-start --}}
<div
    {{
        $attributes->class([
            'swiper-slide hero-item relative w-full min-w-0 max-w-full overflow-hidden',
            'swiper-slide-selected' => $first,
        ])
    }}
>
    <div
        @class([
            'swiper-slide-inner relative flex min-h-full w-full min-w-0 max-w-full overflow-hidden',
            ...(
                ! $backgroundColor && $color
                ? [
                    'bg-gradient-to-t',
                    'from-gray-600/60 to-gray-800/70 dark:from-gray-800/80 dark:to-gray-900/80' => $color === 'dark',
                    'from-white/90 to-[#fbfaf7] dark:from-white/90 dark:to-[#fbfaf7]' => $color === 'light',
                ]
                : []
            ),
        ])
        @style([
              "background-color: {$backgroundColor}" => $backgroundColor,
            ])
        }}
    >
        <x-capell-hero::hero.background :background="$heroBackground" :instance-key="$backgroundKey" />
        <x-capell-hero::hero.media :media="$heroMedia" :alt="$title" />

        @if ($backgroundImage)
            <x-capell::media
                format="webp"
                :media="$backgroundImage"
                :alt="$title"
                height="auto"
                :loading="$first ? 'eager' : 'lazy'"
                :class="
                    Illuminate\Support\Arr::toCssClasses([
                        'hero-bg-img inset-0 h-full w-full pointer-events-none mix-blend-exclusion',
                        $slideBgImgClass ?? '',
                        $backgroundAttachment !== 'fixed' ? 'absolute' : '',
                        $backgroundAttachment === 'fixed' ? 'object-fixed' : '',
                        $backgroundSize === 'contain' ? 'object-contain' : '',
                        $backgroundSize === 'cover' ? 'object-cover' : '',
                        $backgroundPosition === 'center' ? 'object-center' : '',
                        $backgroundPosition === 'top' ? 'object-top' : '',
                        $backgroundPosition === 'right' ? 'object-right' : '',
                        $backgroundPosition === 'bottom' ? 'object-bottom' : '',
                        $backgroundPosition === 'left' ? 'object-left' : '',
                    ])
                "
            />
        @endif

        @if ($backgroundOverlay)
            <div
                class="pointer-events-none absolute inset-0 bg-black/50 mix-blend-multiply dark:bg-gray-900/60"
            ></div>
        @endif

        @if ($slot->isNotEmpty())
            <div @class(['relative grid w-full min-w-0 max-w-full overflow-hidden', $containerClass])>
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
{{-- format-ignore-end --}}

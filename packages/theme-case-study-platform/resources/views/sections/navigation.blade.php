@php
    $brandName = data_get($section, 'brandName', data_get($section, 'brand', __('capell-theme-case-study-platform::sections.navigation.brand')));
    $links = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $link): bool => filled(data_get($link, 'label')))
        ->values();

    if ($links->isEmpty()) {
        $links = collect([
            ['label' => __('capell-theme-case-study-platform::sections.navigation.projects'), 'url' => '#project-feed'],
            ['label' => __('capell-theme-case-study-platform::sections.navigation.disciplines'), 'url' => '#discipline-filters'],
            ['label' => __('capell-theme-case-study-platform::sections.navigation.creators'), 'url' => '#creator-hero'],
            ['label' => __('capell-theme-case-study-platform::sections.navigation.process'), 'url' => '#process-notes'],
        ]);
    }

    $ctaLabel = data_get($section, 'ctaLabel');
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'consultationUrl'));
@endphp

<nav class="csp-masthead">
    <div class="csp-masthead-inner">
        <a
            href="/"
            class="csp-masthead-brand"
        >
            <span class="csp-masthead-wordmark">{{ $brandName }}</span>
            <span class="csp-masthead-tagline">
                {{ __('capell-theme-case-study-platform::sections.hero.kicker') }}
            </span>
        </a>

        <ul class="csp-masthead-nav">
            @foreach ($links as $link)
                <li>
                    <a
                        href="{{ data_get($link, 'url', data_get($link, 'href', '/')) }}"
                    >
                        {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                    </a>
                </li>
            @endforeach
        </ul>

        @if (filled($ctaLabel) && filled($ctaUrl))
            <a
                class="csp-masthead-cta"
                href="{{ $ctaUrl }}"
            >
                {{ $ctaLabel }}
            </a>
        @endif
    </div>
</nav>

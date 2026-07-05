@php
    $heading = data_get($section, 'heading', __('capell-theme-open-studio::sections.creator_hero.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-open-studio::sections.creator_hero.summary'));
    $items = data_get($section, 'items', []);
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'image'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'imageAlt', $heading));
@endphp

<section
    id="creator-hero"
    class="csp-section csp-section-dark"
>
    <div class="csp-section-inner csp-creator-band">
        @if (filled($mediaUrl))
            <img
                src="{{ $mediaUrl }}"
                alt="{{ $mediaAlt }}"
                loading="lazy"
                decoding="async"
                class="csp-cover csp-cover-tall"
            />
        @else
            <div
                class="csp-cover csp-cover-tall csp-cover-empty"
                aria-hidden="true"
            ></div>
        @endif

        <div>
            <p class="csp-kicker">
                {{ __('capell-theme-open-studio::sections.creator_hero.heading') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="csp-lede">{{ $summary }}</p>

            @if (is_iterable($items) && collect($items)->isNotEmpty())
                <div class="csp-creator-list">
                    @foreach ($items as $item)
                        <div class="csp-creator-row">
                            <h3>
                                {{ data_get($item, 'title', data_get($item, 'name', __('capell-theme-open-studio::sections.creator_hero.default_role'))) }}
                            </h3>
                            <p>
                                {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

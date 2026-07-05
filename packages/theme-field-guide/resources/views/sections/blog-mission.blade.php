@php
    $heading = data_get($section, 'heading', __('capell-theme-field-guide::sections.mission.heading'));
    $statement = data_get($section, 'summary', data_get($section, 'statement', __('capell-theme-field-guide::sections.mission.statement')));
    $fallbackItems = __('capell-theme-field-guide::sections.mission.items');
    $items = collect(data_get($section, 'items', data_get($section, 'stories', is_array($fallbackItems) ? $fallbackItems : [])))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
@endphp

<section
    id="blog-mission"
    class="fga-section"
>
    <div class="fga-section-inner">
        <div class="fga-mission-grid">
            <div class="fga-section-head-copy">
                <p class="fga-kicker">
                    {{ __('capell-theme-field-guide::sections.mission.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="fga-mission-statement">{{ $statement }}</p>
            </div>

            <div class="fga-mission-list">
                @foreach ($items as $item)
                    <article class="fga-mission-row">
                        <p class="fga-mono-note">
                            {{ data_get($item, 'meta', data_get($item, 'category', str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT))) }}
                        </p>
                        <h3>
                            @if (filled(data_get($item, 'url', data_get($item, 'href'))))
                                <a
                                    class="fga-title-link"
                                    href="{{ data_get($item, 'url', data_get($item, 'href')) }}"
                                >
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                </a>
                            @else
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            @endif
                        </h3>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>

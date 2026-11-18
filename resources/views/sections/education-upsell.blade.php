@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;

    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
    $kicker = data_get($section, 'kicker');
    $heading = data_get($section, 'heading');
    $summary = data_get($section, 'summary');
@endphp

@if ($items->isNotEmpty())
    <section
        id="learn"
        class="ppc-section ppc-section-field"
    >
        <div class="ppc-section-inner">
            @if (filled($kicker))
                <p class="ppc-kicker">{{ $kicker }}</p>
            @endif

            @if (filled($heading))
                <h2>{{ $heading }}</h2>
            @endif

            @if (filled($summary))
                <p class="ppc-lede">{{ $summary }}</p>
            @endif
            <div class="ppc-grid">
                @foreach ($items as $item)
                    @php
                        $itemUrl = PublicUrlSanitizer::sanitize(data_get($item, 'url', data_get($item, 'href')));
                    @endphp

                    <article class="ppc-card ppc-course-card">
                        <h3>
                            @if (filled($itemUrl))
                                <a
                                    class="ppc-title-link"
                                    href="{{ $itemUrl }}"
                                >
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                </a>
                            @else
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            @endif
                        </h3>
                        @if (filled(data_get($item, 'summary')))
                            <p>{{ data_get($item, 'summary') }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif

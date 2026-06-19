@php
    $items = data_get($section, 'items', data_get($section, 'features', []));
    $heading = data_get($section, 'heading', data_get($section, 'title', ''));
    $summary = data_get($section, 'summary', data_get($section, 'description', ''));
@endphp

<section class="theme-section theme-section-generic">
    <div class="theme-section-inner">
        @if ($heading !== '')
            <h2>{{ $heading }}</h2>
        @endif

        @if ($summary !== '')
            <p>{{ $summary }}</p>
        @endif

        @if (is_iterable($items))
            <div class="theme-card-grid">
                @foreach ($items as $item)
                    <article class="theme-card">
                        <h3>
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'quote', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

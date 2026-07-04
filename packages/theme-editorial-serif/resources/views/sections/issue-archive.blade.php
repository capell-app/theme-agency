@php
    $heading = data_get($section, 'heading', __('capell-theme-editorial-serif::sections.issue_archive.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-editorial-serif::sections.issue_archive.summary'));
    $items = collect(data_get($section, 'items', data_get($section, 'issues', [])));
@endphp

<section
    id="issue-archive"
    class="eser-section eser-section-shade"
>
    <div class="eser-section-inner">
        <p class="eser-eyebrow">
            {{ __('capell-theme-editorial-serif::sections.issue_archive.eyebrow') }}
        </p>
        <h2>{{ $heading }}</h2>
        <hr class="eser-heading-rule" />
        @if (filled($summary))
            <p class="eser-summary">{{ $summary }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div class="eser-list">
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    @endphp

                    <article class="eser-list-item">
                        <h3 class="eser-list-title">
                            @if (filled($itemUrl))
                                <a
                                    class="eser-title-link"
                                    href="{{ $itemUrl }}"
                                >
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                </a>
                            @else
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            @endif
                        </h3>
                        <p class="eser-list-body">
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

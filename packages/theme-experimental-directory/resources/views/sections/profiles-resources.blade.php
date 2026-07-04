@php
    $heading = data_get($section, 'heading', __('capell-theme-experimental-directory::sections.profiles.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-experimental-directory::sections.profiles.summary'));
    $items = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-experimental-directory::sections.profiles.entry_title'), 'summary' => __('capell-theme-experimental-directory::sections.profiles.entry_summary'), 'meta' => __('capell-theme-experimental-directory::sections.profiles.entry_meta')],
    ]));
@endphp

<section
    id="profiles-resources"
    class="exd-section"
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-experimental-directory::sections.profiles.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="exd-lede">{{ $summary }}</p>

        <div class="exd-grid">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                    $initial = mb_strtoupper(mb_substr($itemTitle, 0, 1));
                @endphp

                <article class="exd-card">
                    <div class="exd-card-body exd-profile-card">
                        <span
                            class="exd-profile-mark"
                            aria-hidden="true"
                        >
                            {{ $initial }}
                        </span>
                        <div>
                            <p class="exd-meta">
                                {{ data_get($item, 'meta', data_get($item, 'category', '')) }}
                            </p>
                            <h3>
                                @if (filled($itemUrl))
                                    <a
                                        class="exd-title-link"
                                        href="{{ $itemUrl }}"
                                    >
                                        {{ $itemTitle }}
                                    </a>
                                @else
                                    {{ $itemTitle }}
                                @endif
                            </h3>
                            <p>
                                {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                            </p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

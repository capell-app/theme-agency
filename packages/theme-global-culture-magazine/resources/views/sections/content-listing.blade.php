@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section class="gcm-section">
    <div class="gcm-section-inner">
        <div class="gcm-intro">
            <p class="gcm-kicker">
                {{ __('capell-theme-global-culture-magazine::sections.listing.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-global-culture-magazine::sections.listing.heading')) }}
            </h2>
            @if (data_get($section, 'summary', '') !== '')
                <p class="gcm-lede">
                    {{ data_get($section, 'summary') }}
                </p>
            @endif
        </div>

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <ol class="gcm-index">
                @foreach ($items as $item)
                    <li class="gcm-index-row">
                        <span
                            class="gcm-index-number"
                            aria-hidden="true"
                        >
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <p class="gcm-meta">
                            {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
                        </p>
                        <div>
                            @php
                                $itemTitle = (string) data_get($item, 'title', data_get($item, 'name', ''));
                                $itemUrl = (string) data_get($item, 'url', data_get($item, 'href', ''));
                            @endphp

                            <h3>
                                @if ($itemUrl !== '')
                                    <a
                                        class="gcm-title-link"
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
                    </li>
                @endforeach
            </ol>
        @else
            <p class="gcm-lede">
                {{ __('capell-theme-global-culture-magazine::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>

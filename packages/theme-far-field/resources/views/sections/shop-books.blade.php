@php
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-far-field::sections.shop.bookshelf_title'), 'summary' => __('capell-theme-far-field::sections.shop.bookshelf_summary'), 'meta' => __('capell-theme-far-field::sections.shop.bookshelf_meta')],
        ['title' => __('capell-theme-far-field::sections.shop.travel_desk_title'), 'summary' => __('capell-theme-far-field::sections.shop.travel_desk_summary'), 'meta' => __('capell-theme-far-field::sections.shop.travel_desk_meta')],
        ['title' => __('capell-theme-far-field::sections.shop.books_title'), 'summary' => __('capell-theme-far-field::sections.shop.books_summary'), 'meta' => __('capell-theme-far-field::sections.shop.books_meta')],
    ]));
@endphp

<section
    class="gcm-section gcm-section-tinted"
    id="shop-books"
>
    <div class="gcm-section-inner">
        <div class="gcm-intro">
            <p class="gcm-kicker">
                {{ __('capell-theme-far-field::sections.shop.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-far-field::sections.shop.heading')) }}
            </h2>
            @if (data_get($section, 'summary', '') !== '')
                <p class="gcm-lede">{{ data_get($section, 'summary') }}</p>
            @endif
        </div>

        <ol class="gcm-index">
            @foreach ($stories as $story)
                <li class="gcm-index-row">
                    <span
                        class="gcm-index-number"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <p class="gcm-meta">
                        {{ data_get($story, 'meta', data_get($story, 'category', '')) }}
                    </p>
                    <div>
                        @php
                            $storyTitle = (string) data_get($story, 'title', data_get($story, 'name', ''));
                            $storyUrl = (string) data_get($story, 'url', data_get($story, 'href', ''));
                        @endphp

                        <h3>
                            @if ($storyUrl !== '')
                                <a
                                    class="gcm-title-link"
                                    href="{{ $storyUrl }}"
                                >
                                    {{ $storyTitle }}
                                </a>
                            @else
                                {{ $storyTitle }}
                            @endif
                        </h3>
                        <p>
                            {{ data_get($story, 'summary', data_get($story, 'description', '')) }}
                        </p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>

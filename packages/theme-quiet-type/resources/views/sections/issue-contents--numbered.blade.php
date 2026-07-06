@php
    $heading = data_get($section, 'heading', __('capell-theme-quiet-type::sections.issue_contents.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-quiet-type::sections.issue_contents.summary'));
    $issueLabel = data_get($section, 'issueLabel', data_get($section, 'issue_label'));
    $items = collect(data_get($section, 'items', data_get($section, 'entries', [])));
@endphp

{{--
    issue-contents-table-of-contents, numbered variant — a flat running
    numeral listing rather than section-grouped headings, closer to a
    printed masthead's plain page listing.
--}}
<section
    id="issue-contents"
    class="eser-section eser-section-shade"
>
    <div class="eser-section-inner">
        <p class="eser-eyebrow">
            {{ __('capell-theme-quiet-type::sections.issue_contents.eyebrow') }}
            @if (filled($issueLabel))
                &middot; {{ $issueLabel }}
            @endif
        </p>
        <h2>{{ $heading }}</h2>
        <hr class="eser-heading-rule" />
        @if (filled($summary))
            <p class="eser-summary">{{ $summary }}</p>
        @endif

        @if ($items->isNotEmpty())
            <ol class="eser-toc-numbered">
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $author = data_get($item, 'author', data_get($item, 'byline'));
                    @endphp
                    <li class="eser-toc-item">
                        <span
                            class="eser-index-numeral"
                            aria-hidden="true"
                        >
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="eser-toc-title">
                            @if (filled($itemUrl))
                                <a
                                    class="eser-title-link"
                                    href="{{ $itemUrl }}"
                                >
                                    {{ data_get($item, 'title', '') }}
                                </a>
                            @else
                                {{ data_get($item, 'title', '') }}
                            @endif
                        </span>
                        @if (filled($author))
                            <span class="eser-toc-author">{{ $author }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</section>

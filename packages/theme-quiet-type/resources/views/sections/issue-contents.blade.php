@php
    $heading = data_get($section, 'heading', __('capell-theme-quiet-type::sections.issue_contents.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-quiet-type::sections.issue_contents.summary'));
    $issueLabel = data_get($section, 'issueLabel', data_get($section, 'issue_label'));
    $items = collect(data_get($section, 'items', data_get($section, 'entries', [])));
@endphp

{{--
    issue-contents-table-of-contents (Part 2 §B) — a single issue's table
    of contents: section-grouped list of every piece in that issue, each
    entry carrying its author and a same-page or cross-page anchor. Default
    variant groups by `section` label; the `numbered` variant
    (issue-contents--numbered.blade.php) drops the section grouping in
    favour of a flat running numeral, closer to a printed masthead's page
    listing.
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
            @php
                $grouped = $items->groupBy(fn (mixed $item): string => (string) data_get($item, 'section', __('capell-theme-quiet-type::sections.issue_contents.ungrouped')));
            @endphp

            @foreach ($grouped as $groupLabel => $groupItems)
                <div class="eser-toc-group">
                    <p class="eser-toc-group-label">{{ $groupLabel }}</p>
                    <ul class="eser-toc-list">
                        @foreach ($groupItems as $item)
                            @php
                                $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                                $author = data_get($item, 'author', data_get($item, 'byline'));
                            @endphp
                            <li class="eser-toc-item">
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
                                    <span
                                        class="eser-toc-author"
                                        >{{ $author }}</span
                                    >
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        @endif
    </div>
</section>

@php
    $name = data_get($section, 'name', __('capell-theme-ink-press::sections.credibility.name'));
    $role = data_get($section, 'role', __('capell-theme-ink-press::sections.credibility.role'));
    $credentials = collect(data_get($section, 'credentials', [
        __('capell-theme-ink-press::sections.credibility.credential_one'),
        __('capell-theme-ink-press::sections.credibility.credential_two'),
    ]));
@endphp

{{-- Byline variant: a single-line inline credential strip for stories where
     the full bio card would crowd the column — still scroll-triggered via
     the same .dnews-credibility bloom, just a terser layout. --}}
<aside
    id="author-credibility-inline"
    class="dnews-credibility dnews-credibility-byline"
>
    <p class="dnews-credibility-byline-line">
        <span class="dnews-credibility-name">{{ $name }}</span>
        <span class="dnews-meta">{{ $role }}</span>
        @if ($credentials->isNotEmpty())
            <span class="dnews-meta">· {{ $credentials->implode(' · ') }}</span>
        @endif
    </p>
</aside>

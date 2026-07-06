@php
    $name = data_get($section, 'name', __('capell-theme-ink-press::sections.credibility.name'));
    $role = data_get($section, 'role', __('capell-theme-ink-press::sections.credibility.role'));
    $bio = data_get($section, 'bio', __('capell-theme-ink-press::sections.credibility.bio'));
    $credentials = collect(data_get($section, 'credentials', [
        __('capell-theme-ink-press::sections.credibility.credential_one'),
        __('capell-theme-ink-press::sections.credibility.credential_two'),
    ]));
@endphp

{{-- Credentials reveal inline at a narrative point via a scroll-driven
     `animation-timeline: view()` bloom (see .dnews-credibility) rather than
     a sticky sidebar; `:target` also expands it directly when a story links
     straight to `#author-credibility-inline`. Static and fully readable
     either way — the animation only affects entrance timing. --}}
<aside
    id="author-credibility-inline"
    class="dnews-credibility"
>
    <p class="dnews-meta dnews-meta-accent">
        {{ __('capell-theme-ink-press::sections.credibility.kicker') }}
    </p>
    <div class="dnews-credibility-row">
        <p class="dnews-credibility-name">{{ $name }}</p>
        <p class="dnews-meta">{{ $role }}</p>
    </div>
    <p class="dnews-credibility-bio">{{ $bio }}</p>
    <ul class="dnews-credibility-list">
        @foreach ($credentials as $credential)
            <li>{{ $credential }}</li>
        @endforeach
    </ul>
</aside>

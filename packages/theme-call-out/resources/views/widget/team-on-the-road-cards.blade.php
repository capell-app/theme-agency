@php
    /**
     * team-on-the-road-cards — team member cards.
     *
     * Two variants (theme-bar criterion 3), branched on payload `variant`:
     * "default" (photo, name, role, one-line bio) and "credentials" (adds a
     * small list of per-member licences/certifications under the bio, for
     * trades where the individual technician's qualification is part of the
     * trust story, not just the company's).
     */
    $heading = $widget->getMeta('heading', __('capell-theme-call-out::sections.team.heading'));
    $summary = $widget->getMeta('summary', __('capell-theme-call-out::sections.team.summary'));
    $variant = $widget->getMeta('variant', 'default');
    $members = collect($widget->getMeta('members', []))->take(20);
@endphp

<section
    id="team-on-the-road-cards"
    class="rco-shell rco-section"
    data-widget="team-on-the-road-cards"
    data-variant="{{ $variant }}"
>
    <div class="rco-section-inner">
        <h2>{{ $heading }}</h2>
        <p class="rco-section-summary">{{ $summary }}</p>

        <ul class="rco-team-grid">
            @foreach ($members as $member)
                <li class="rco-team-card">
                    @if (filled(data_get($member, 'photo')))
                        <img
                            src="{{ data_get($member, 'photo') }}"
                            alt="{{ data_get($member, 'name', '') }}"
                            loading="lazy"
                            decoding="async"
                            class="rco-team-photo"
                        />
                    @endif
                    <h3>{{ data_get($member, 'name', '') }}</h3>
                    <p class="rco-team-role">{{ data_get($member, 'role', '') }}</p>
                    <p class="rco-team-bio">{{ data_get($member, 'bio', '') }}</p>

                    @if ($variant === 'credentials' && filled(data_get($member, 'credentials')))
                        <ul class="rco-team-credentials">
                            @foreach ((array) data_get($member, 'credentials', []) as $credential)
                                <li>{{ $credential }}</li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</section>

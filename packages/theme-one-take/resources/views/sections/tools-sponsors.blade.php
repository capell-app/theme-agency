{{--
    tools-sponsors-sidebar (Wave 4c signature widget, mechanic: dossier
    pages). Rendered as the dossier's appendix: a bound side-column list of
    the tools and sponsors credited across the gallery, each entry keeping
    an "appendix note" number so it reads as a cross-reference rather than
    a generic partner-logo strip. This is the "default" (stacked appendix)
    variant; "--rail" arranges the same payload as a sticky side rail next
    to a lead note, for pages with more vertical room.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-one-take::sections.events.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-one-take::sections.events.summary'));
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-one-take::sections.events.planning_title'), 'summary' => __('capell-theme-one-take::sections.events.planning_summary'), 'meta' => __('capell-theme-one-take::sections.events.planning_meta')],
        ['title' => __('capell-theme-one-take::sections.events.workshop_title'), 'summary' => __('capell-theme-one-take::sections.events.workshop_summary'), 'meta' => __('capell-theme-one-take::sections.events.workshop_meta')],
        ['title' => __('capell-theme-one-take::sections.events.systems_title'), 'summary' => __('capell-theme-one-take::sections.events.systems_summary'), 'meta' => __('capell-theme-one-take::sections.events.systems_meta')],
    ]));
@endphp

<section
    id="tools-sponsors"
    class="ops-section"
>
    <div class="ops-section-inner">
        <p class="ops-kicker">
            {{ __('capell-theme-one-take::sections.events.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        @if (filled($summary))
            <p class="ops-lede">{{ $summary }}</p>
        @endif

        <ol class="ops-dossier-appendix">
            @foreach ($stories as $index => $story)
                <li class="ops-dossier-appendix-entry">
                    <span
                        class="ops-dossier-appendix-mark"
                        aria-hidden="true"
                    >
                        {{ __('capell-theme-one-take::sections.events.appendix_mark', ['letter' => chr(65 + ($index % 26))]) }}
                    </span>
                    <article>
                        <p class="ops-meta">
                            {{ data_get($story, 'meta', data_get($story, 'category', '')) }}
                        </p>
                        <h3>
                            {{ data_get($story, 'title', data_get($story, 'name', '')) }}
                        </h3>
                        <p>
                            {{ data_get($story, 'summary', data_get($story, 'description', '')) }}
                        </p>
                    </article>
                </li>
            @endforeach
        </ol>
    </div>
</section>

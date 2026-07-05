@php
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-front-row::sections.events.planning_title'), 'summary' => __('capell-theme-front-row::sections.events.planning_summary')],
        ['title' => __('capell-theme-front-row::sections.events.workshop_title'), 'summary' => __('capell-theme-front-row::sections.events.workshop_summary')],
        ['title' => __('capell-theme-front-row::sections.events.systems_title'), 'summary' => __('capell-theme-front-row::sections.events.systems_summary')],
    ]));
@endphp

<section
    id="creators"
    class="ppc-section"
>
    <div class="ppc-section-inner">
        <p class="ppc-kicker">
            {{ __('capell-theme-front-row::sections.events.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-front-row::sections.events.heading')) }}
        </h2>
        <div class="ppc-grid">
            @foreach ($stories as $story)
                @php
                    $storyImage = data_get($story, 'image', data_get($story, 'imageUrl'));
                    $storyAlt = data_get($story, 'imageAlt', data_get($story, 'title', ''));
                    $storyUrl = data_get($story, 'url', data_get($story, 'href'));
                @endphp

                <article class="ppc-card">
                    <div class="ppc-creator-head">
                        @if (filled($storyImage))
                            <img
                                src="{{ $storyImage }}"
                                alt="{{ $storyAlt }}"
                                loading="lazy"
                                decoding="async"
                                width="56"
                                height="56"
                                class="ppc-avatar"
                            />
                        @else
                            <div
                                class="ppc-avatar"
                                aria-hidden="true"
                            ></div>
                        @endif
                        <div>
                            <p class="ppc-meta">
                                {{ data_get($story, 'meta', data_get($story, 'category', '')) }}
                            </p>
                            <h3>
                                @if (filled($storyUrl))
                                    <a
                                        class="ppc-title-link"
                                        href="{{ $storyUrl }}"
                                    >
                                        {{ data_get($story, 'title', data_get($story, 'name', '')) }}
                                    </a>
                                @else
                                    {{ data_get($story, 'title', data_get($story, 'name', '')) }}
                                @endif
                            </h3>
                        </div>
                    </div>
                    <p>
                        {{ data_get($story, 'summary', data_get($story, 'description', '')) }}
                    </p>
                    @if (filled(data_get($story, 'stat_label')))
                        <div class="ppc-stat-row">
                            <span>
                                <strong>
                                    {{ data_get($story, 'stat_value', '') }}
                                </strong>
                                {{ data_get($story, 'stat_label') }}
                            </span>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>

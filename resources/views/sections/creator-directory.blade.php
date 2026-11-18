@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;

    $items = collect(data_get($section, 'items', data_get($section, 'stories', [])))
        ->filter(fn (mixed $story): bool => filled(data_get($story, 'title', data_get($story, 'name'))))
        ->values();
    $kicker = data_get($section, 'kicker');
    $heading = data_get($section, 'heading');
    $summary = data_get($section, 'summary');
@endphp

@if ($items->isNotEmpty())
    <section
        id="creators"
        class="ppc-section"
    >
        <div class="ppc-section-inner">
            @if (filled($kicker))
                <p class="ppc-kicker">{{ $kicker }}</p>
            @endif

            @if (filled($heading))
                <h2>{{ $heading }}</h2>
            @endif

            @if (filled($summary))
                <p class="ppc-lede">{{ $summary }}</p>
            @endif

            <div class="ppc-grid">
                @foreach ($items as $story)
                    @php
                        $storyImage = PublicUrlSanitizer::sanitize(data_get($story, 'image', data_get($story, 'imageUrl')));
                        $storyAlt = data_get($story, 'imageAlt', data_get($story, 'title', ''));
                        $storyUrl = PublicUrlSanitizer::sanitize(data_get($story, 'url', data_get($story, 'href')));
                    @endphp

                    <article
                        @if (filled(data_get($story, 'id'))) id="{{ data_get($story, 'id') }}" @endif
                        class="ppc-card"
                    >
                        <div class="ppc-creator-head">
                            @if (filled($storyImage))
                                <x-capell::image-source
                                    :image="$storyImage"
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
                                @if (filled(data_get($story, 'meta', data_get($story, 'category'))))
                                    <p class="ppc-meta">{{ data_get($story, 'meta', data_get($story, 'category')) }}</p>
                                @endif
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
                        @if (filled(data_get($story, 'summary', data_get($story, 'description'))))
                            <p>{{ data_get($story, 'summary', data_get($story, 'description')) }}</p>
                        @endif
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
@endif

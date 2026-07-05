@php
    $heading = data_get($section, 'heading', __('capell-theme-first-light::sections.feed_hero.heading'));
    $summary = data_get($section, 'summary');
    $feedDate = data_get($section, 'date', now()->format('j F Y'));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));
    $notes = collect(data_get($section, 'notes', data_get($section, 'items', [])))
        ->filter(fn (mixed $note): bool => filled(data_get($note, 'title')))
        ->values();
@endphp

<section
    id="feed-hero"
    class="mcf-section"
>
    <div class="mcf-section-inner">
        <p class="mcf-live">
            <span
                class="mcf-live-dot"
                aria-hidden="true"
            ></span>
            {{ __('capell-theme-first-light::sections.feed_hero.updated') }} {{ $feedDate }}
        </p>
        <h2>{{ $heading }}</h2>
        @if (filled($summary))
            <p class="mcf-lede">{{ $summary }}</p>
        @endif

        @if (filled($mediaUrl))
            <figure
                class="mcf-capture"
                style="margin-top: clamp(2rem, 4vw, 3rem)"
            >
                <img
                    src="{{ $mediaUrl }}"
                    alt="{{ $mediaAlt ?? $heading }}"
                    width="1200"
                    height="750"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="mcf-capture-media"
                />
                <figcaption class="mcf-capture-caption">
                    <span>{{ $mediaAlt ?? $heading }}</span>
                    <span class="mcf-meta">{{ $feedDate }}</span>
                </figcaption>
            </figure>
        @endif

        @if ($notes->isNotEmpty())
            <div class="mcf-tab-notes">
                @foreach ($notes as $note)
                    <div class="mcf-tab-note">
                        <p class="mcf-meta">
                            {{ data_get($note, 'title', '') }}
                        </p>
                        <p class="mcf-tiny">
                            {{ data_get($note, 'summary', data_get($note, 'description', '')) }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

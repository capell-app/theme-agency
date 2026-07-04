@php
    $items = data_get($section, 'items', [
        ['title' => 'Saved collections', 'summary' => 'Group the pages you love into named boards and share them with the whole team.'],
        ['title' => 'Votes & comments', 'summary' => 'See what the community rates highest and read the notes on what makes each page work.'],
        ['title' => 'Live template prices', 'summary' => 'Every paid template shows its current price so you can budget the build before you start.'],
    ]);
@endphp

<section
    id="gallery-system"
    class="lga-section lga-section-dark"
>
    <div class="lga-section-inner">
        <p class="lga-eyebrow">
            {{ __('capell-theme-landing-gallery::sections.gallery_system.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-landing-gallery::sections.gallery_system.heading')) }}
        </h2>
        <p class="lga-lede">
            {{ data_get($section, 'summary', __('capell-theme-landing-gallery::sections.gallery_system.summary')) }}
        </p>

        <div class="lga-grid">
            @foreach ($items as $item)
                <article class="lga-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

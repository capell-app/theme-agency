@php
    /**
     * review-proof-wall — customer reviews grid, capped at <= 50 (§0.3
     * payload cap for client-side-filterable-scale grids).
     *
     * Two variants (theme-bar criterion 3), branched on payload `variant`:
     * "default" (grid of review cards) and "featured" (one large pull-quote
     * review followed by a smaller supporting grid).
     */
    $heading = $widget->getMeta('heading', __('capell-theme-call-out::sections.reviews.heading'));
    $summary = $widget->getMeta('summary', __('capell-theme-call-out::sections.reviews.summary'));
    $variant = $widget->getMeta('variant', 'default');
    $reviews = collect($widget->getMeta('reviews', []))->take(50);
    $featuredReview = $variant === 'featured' ? $reviews->first() : null;
    $remainingReviews = $variant === 'featured' ? $reviews->skip(1) : $reviews;
@endphp

<section
    id="review-proof-wall"
    class="rco-shell rco-section"
    data-widget="review-proof-wall"
    data-variant="{{ $variant }}"
>
    <div class="rco-section-inner">
        <h2>{{ $heading }}</h2>
        <p class="rco-section-summary">{{ $summary }}</p>

        @if ($featuredReview)
            <blockquote class="rco-review-featured">
                <p>&ldquo;{{ data_get($featuredReview, 'quote', '') }}&rdquo;</p>
                <cite>
                    {{ data_get($featuredReview, 'author', '') }}
                    <span
                        class="rco-review-rating"
                        aria-label="{{ data_get($featuredReview, 'rating', 5) }} out of 5"
                    >
                        {{ str_repeat('★', (int) data_get($featuredReview, 'rating', 5)) }}
                    </span>
                </cite>
            </blockquote>
        @endif

        <ul class="rco-review-grid">
            @foreach ($remainingReviews as $review)
                <li class="rco-review-card">
                    <p
                        class="rco-review-rating"
                        aria-label="{{ data_get($review, 'rating', 5) }} out of 5"
                    >
                        {{ str_repeat('★', (int) data_get($review, 'rating', 5)) }}
                    </p>
                    <p class="rco-review-quote">&ldquo;{{ data_get($review, 'quote', '') }}&rdquo;</p>
                    <p class="rco-review-author">
                        {{ data_get($review, 'author', '') }}
                    </p>
                </li>
            @endforeach
        </ul>
    </div>
</section>

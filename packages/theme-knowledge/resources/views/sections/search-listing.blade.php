<section class="theme-section theme-section-search-listing">
    @isset($heading)
        <h2>{{ $heading }}</h2>
    @endisset

    <p>
        {{ $searchAvailable ?? false ? 'Connected search index' : 'Static search guide' }}
    </p>
</section>

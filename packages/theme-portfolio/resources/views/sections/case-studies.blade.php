<section class="theme-section theme-section-case-studies">
    @isset($heading)
        <h2>{{ $heading }}</h2>
    @endisset

    <p>
        {{ $contentSectionsAvailable ?? false ? 'Connected case-study library' : 'Static case studies' }}
    </p>
</section>

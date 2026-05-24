<section class="theme-section theme-section-work-grid">
    @isset($heading)
        <h2>{{ $heading }}</h2>
    @endisset

    <p>
        {{ $mediaLibraryAvailable ?? false ? 'Connected media library' : 'Static work grid' }}
    </p>
</section>

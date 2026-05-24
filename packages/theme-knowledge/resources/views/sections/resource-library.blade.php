<section class="theme-section theme-section-resource-library">
    @isset($heading)
        <h2>{{ $heading }}</h2>
    @endisset

    <p>
        {{ $blogAvailable ?? false ? 'Connected article library' : 'Static resource library' }}
    </p>
</section>

<section class="theme-section theme-section-volunteer-donate">
    @isset($heading)
        <h2>{{ $heading }}</h2>
    @endisset

    <p>
        {{ $formBuilderAvailable ?? false ? 'Connected supporter form' : 'Static supporter CTA' }}
    </p>
</section>

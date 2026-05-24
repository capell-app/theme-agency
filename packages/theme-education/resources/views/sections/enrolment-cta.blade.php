<section class="theme-section theme-section-enrolment-cta">
    @isset($heading)
        <h2>{{ $heading }}</h2>
    @endisset

    <p>
        {{ $formBuilderAvailable ?? false ? 'Connected enrolment form' : 'Static enrolment CTA' }}
    </p>
</section>

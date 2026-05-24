<section class="theme-section theme-section-newsletter">
    @isset($heading)
        <h2>{{ $heading }}</h2>
    @endisset

    <p>
        {{ $newsletterAvailable ?? false ? 'Connected newsletter signup' : 'Static newsletter CTA' }}
    </p>
</section>

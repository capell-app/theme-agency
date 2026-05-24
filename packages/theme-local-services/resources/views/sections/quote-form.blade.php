<section class="theme-section theme-section-quote-form">
    @isset($heading)
        <h2>{{ $heading }}</h2>
    @endisset

    <p>
        {{ $formBuilderAvailable ?? false ? 'Connected enquiry workflow' : 'Static enquiry path' }}
    </p>
</section>

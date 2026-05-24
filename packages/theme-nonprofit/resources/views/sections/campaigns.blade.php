<section class="theme-section theme-section-campaigns">
    @isset($heading)
        <h2>{{ $heading }}</h2>
    @endisset

    <p>
        {{ $campaignStudioAvailable ?? false ? 'Connected campaign workflow' : 'Static campaigns' }}
    </p>
</section>

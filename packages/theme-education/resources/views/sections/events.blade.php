<section class="theme-section theme-section-events">
    @isset($heading)
        <h2>{{ $heading }}</h2>
    @endisset

    <p>
        {{ $eventsAvailable ?? false ? 'Connected events calendar' : 'Static events list' }}
    </p>
</section>

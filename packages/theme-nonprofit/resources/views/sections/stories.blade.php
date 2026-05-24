<section class="theme-section theme-section-stories">
    @isset($heading)
        <h2>{{ $heading }}</h2>
    @endisset

    <p>
        {{ $blogAvailable ?? false ? 'Connected story feed' : 'Static stories' }}
    </p>
</section>

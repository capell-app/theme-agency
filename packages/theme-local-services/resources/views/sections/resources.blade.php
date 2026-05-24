<section class="theme-section theme-section-resources">
    @isset($heading)
        <h2>{{ $heading }}</h2>
    @endisset

    <p>
        {{ $blogAvailable ?? false ? 'Connected resource feed' : 'Static resources' }}
    </p>
</section>

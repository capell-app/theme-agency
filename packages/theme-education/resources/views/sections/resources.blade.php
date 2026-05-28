<section class="theme-section theme-section-resources">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2 class="text-4xl font-black tracking-tight text-[#1d4ed8]">
                {{ $heading }}
            </h2>
        </div>
    @endisset

    <p class="mx-auto mt-4 max-w-5xl px-6 text-stone-600">
        {{ $blogAvailable ?? false ? __('capell-theme-education::generic.resources_connected') : __('capell-theme-education::generic.resources_static') }}
    </p>
</section>

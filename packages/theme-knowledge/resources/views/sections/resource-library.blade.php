<section class="theme-section theme-section-resource-library">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2 class="text-4xl font-black tracking-tight text-[#4b5563]">
                {{ $heading }}
            </h2>
        </div>
    @endisset

    <p class="mx-auto mt-4 max-w-5xl px-6 text-stone-600">
        {{ $blogAvailable ?? false ? __('capell-theme-knowledge::generic.resource_library_connected') : __('capell-theme-knowledge::generic.resource_library_static') }}
    </p>
</section>

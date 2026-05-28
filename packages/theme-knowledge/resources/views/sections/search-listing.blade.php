<section class="theme-section theme-section-search-listing">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2 class="text-4xl font-black tracking-tight text-[#4b5563]">
                {{ $heading }}
            </h2>
        </div>
    @endisset

    <p class="mx-auto mt-4 max-w-5xl px-6 text-stone-600">
        {{ $searchAvailable ?? false ? __('capell-theme-knowledge::generic.search_connected') : __('capell-theme-knowledge::generic.search_static') }}
    </p>
</section>

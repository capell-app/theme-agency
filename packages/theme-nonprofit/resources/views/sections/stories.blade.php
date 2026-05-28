<section class="theme-section theme-section-stories">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2 class="text-4xl font-black tracking-tight text-[#0f172a]">
                {{ $heading }}
            </h2>
        </div>
    @endisset

    <p class="mx-auto mt-4 max-w-5xl px-6 text-stone-600">
        {{ $blogAvailable ?? false ? __('capell-theme-nonprofit::generic.stories_connected') : __('capell-theme-nonprofit::generic.stories_static') }}
    </p>
</section>

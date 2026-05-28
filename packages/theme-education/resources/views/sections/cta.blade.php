<section class="theme-section theme-section-cta">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2 class="text-4xl font-black tracking-tight text-[#1d4ed8]">
                {{ $heading }}
            </h2>
            <p class="mt-4 max-w-2xl text-stone-600">
                {{ __('capell-theme-education::generic.cta_blurb') }}
            </p>
        </div>
    @endisset
</section>

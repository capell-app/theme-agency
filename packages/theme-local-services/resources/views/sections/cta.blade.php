<section class="theme-section theme-section-cta">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <h2 class="text-4xl font-black tracking-tight text-[#17211c]">
                {{ $heading }}
            </h2>
        @endisset

        <p class="mt-4 max-w-2xl text-stone-600">
            {{ __('capell-theme-local-services::generic.cta_copy') }}
        </p>
    </div>
</section>

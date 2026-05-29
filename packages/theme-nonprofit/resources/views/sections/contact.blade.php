<section class="theme-section theme-section-contact bg-[#fff8e7]">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <p
                class="text-xs font-black tracking-[0.18em] text-[#b45309] uppercase"
            >
                {{ __('capell-theme-nonprofit::generic.contact_label') }}
            </p>
            <h2
                class="mt-4 max-w-2xl text-4xl font-black tracking-tight text-[#132014]"
            >
                {{ $heading }}
            </h2>
        @endisset

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ([__('capell-theme-nonprofit::generic.contact_route_donate'), __('capell-theme-nonprofit::generic.contact_route_volunteer'), __('capell-theme-nonprofit::generic.contact_route_partner')] as $route)
                <article class="border border-[#facc15] bg-white p-5">
                    <p
                        class="text-xs font-black tracking-[0.18em] text-[#166534] uppercase"
                    >
                        {{ __('capell-theme-nonprofit::generic.contact_route_label') }}
                    </p>
                    <h3 class="mt-3 text-lg font-black text-[#132014]">
                        {{ $route }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ __('capell-theme-nonprofit::generic.contact_route_summary') }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>

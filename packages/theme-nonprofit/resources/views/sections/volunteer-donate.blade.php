<section class="theme-section theme-section-volunteer-donate bg-[#fff8e7]">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div
            class="grid gap-6 border border-[#facc15] bg-white p-8 md:grid-cols-[0.72fr_1fr] md:items-center"
        >
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#ca8a04] uppercase"
                >
                    {{ __('capell-theme-nonprofit::generic.supporter_cta_label') }}
                </p>
                @isset($heading)
                    <h2
                        class="mt-4 text-4xl font-black tracking-tight text-[#0f172a]"
                    >
                        {{ $heading }}
                    </h2>
                @endisset
            </div>

            <div>
                <p class="text-base leading-7 text-slate-600">
                    {{ $formBuilderAvailable ?? false ? __('capell-theme-nonprofit::generic.volunteer_connected') : __('capell-theme-nonprofit::generic.volunteer_static') }}
                </p>
                <div class="mt-6 grid gap-3 text-sm font-bold sm:grid-cols-3">
                    <span class="bg-[#dcfce7] px-4 py-3 text-[#166534]">
                        {{ __('capell-theme-nonprofit::generic.supporter_step_one') }}
                    </span>
                    <span class="bg-[#fef9c3] px-4 py-3 text-[#854d0e]">
                        {{ __('capell-theme-nonprofit::generic.supporter_step_two') }}
                    </span>
                    <span class="bg-white px-4 py-3 text-[#166534]">
                        {{ __('capell-theme-nonprofit::generic.supporter_step_three') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

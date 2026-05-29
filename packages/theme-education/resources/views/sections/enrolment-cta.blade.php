<section class="theme-section theme-section-enrolment-cta bg-[#f8fbff]">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div
            class="grid gap-6 border border-[#c7d2fe] bg-white p-8 md:grid-cols-[0.75fr_1fr] md:items-center"
        >
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#14b8a6] uppercase"
                >
                    {{ __('capell-theme-education::generic.enrolment_cta_label') }}
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
                    {{ $formBuilderAvailable ?? false ? __('capell-theme-education::generic.enrolment_form_connected') : __('capell-theme-education::generic.enrolment_form_static') }}
                </p>
                <div class="mt-6 grid gap-3 text-sm font-bold sm:grid-cols-3">
                    <span class="bg-[#ecfeff] px-4 py-3 text-[#0f766e]">
                        {{ __('capell-theme-education::generic.enrolment_step_one') }}
                    </span>
                    <span class="bg-[#eef2ff] px-4 py-3 text-[#4338ca]">
                        {{ __('capell-theme-education::generic.enrolment_step_two') }}
                    </span>
                    <span class="bg-[#fff7ed] px-4 py-3 text-[#c2410c]">
                        {{ __('capell-theme-education::generic.enrolment_step_three') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="theme-section theme-section-quote-form bg-[#f8fafc]">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-8 lg:grid-cols-[0.82fr_1fr] lg:items-start">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#ea580c] uppercase"
                >
                    {{ __('capell-theme-local-services::generic.quote_form_label') }}
                </p>
                @isset($heading)
                    <h2
                        class="mt-4 text-3xl font-black tracking-tight text-[#17211c]"
                    >
                        {{ $heading }}
                    </h2>
                @endisset

                <p class="mt-4 max-w-2xl text-slate-600">
                    {{ $formBuilderAvailable ?? false ? __('capell-theme-local-services::generic.quote_form_copy') : __('capell-theme-local-services::generic.quote_form_copy_fallback') }}
                </p>
            </div>

            <div class="border border-[#99f6e4] bg-white p-5 shadow-sm">
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ([
                                  __('capell-theme-local-services::generic.quote_field_service'),
                                  __('capell-theme-local-services::generic.quote_field_area'),
                                  __('capell-theme-local-services::generic.quote_field_urgency'),
                                  __('capell-theme-local-services::generic.quote_field_contact'),
                              ] as $field)
                        <div class="border border-slate-200 bg-[#f8fafc] p-4">
                            <p
                                class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                            >
                                {{ $field }}
                            </p>
                            <span
                                class="mt-4 block h-2 bg-[#ccfbf1]"
                                aria-hidden="true"
                            ></span>
                        </div>
                    @endforeach
                </div>
                <div
                    class="mt-4 flex items-center justify-between border border-[#fed7aa] bg-[#fff7ed] p-4"
                >
                    <p class="text-sm font-black text-[#9a3412]">
                        {{ __('capell-theme-local-services::generic.quote_eta') }}
                    </p>
                    <span class="text-sm font-black text-[#0f766e]">
                        {{ __('capell-theme-local-services::generic.quote_ready') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

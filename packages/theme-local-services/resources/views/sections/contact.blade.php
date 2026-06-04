@php
    $sectionHeading = $heading ?? ($section->heading ?? null);
@endphp

<section
    id="contact"
    class="theme-section theme-section-contact bg-white"
>
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-8 lg:grid-cols-[0.75fr_1fr] lg:items-start">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#0f766e] uppercase"
                >
                    {{ __('capell-theme-local-services::generic.contact_label') }}
                </p>
                @if ($sectionHeading)
                    <h2
                        class="mt-4 text-3xl font-black tracking-tight text-[#17211c]"
                    >
                        {{ $sectionHeading }}
                    </h2>
                @endif

                <p class="mt-4 max-w-2xl text-slate-600">
                    {{ __('capell-theme-local-services::generic.contact_copy') }}
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ([
                              [__('capell-theme-local-services::generic.contact_card_calls'), __('capell-theme-local-services::generic.contact_card_calls_summary')],
                              [__('capell-theme-local-services::generic.contact_card_visits'), __('capell-theme-local-services::generic.contact_card_visits_summary')],
                              [__('capell-theme-local-services::generic.contact_card_quotes'), __('capell-theme-local-services::generic.contact_card_quotes_summary')],
                              [__('capell-theme-local-services::generic.contact_card_aftercare'), __('capell-theme-local-services::generic.contact_card_aftercare_summary')],
                          ] as $card)
                    <article class="border border-slate-200 bg-[#f8fafc] p-5">
                        <p class="text-sm font-black text-[#17211c]">
                            {{ $card[0] }}
                        </p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $card[1] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>

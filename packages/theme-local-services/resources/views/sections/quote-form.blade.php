@php
    $formBuilderAvailable ??= false;
    $sectionHeading = $heading ?? ($section->heading ?? null);
    $formAction = $section->formAction ?? $section->action ?? ($formAction ?? '/contact');
    $formMethod = strtoupper((string) ($section->formMethod ?? $method ?? 'POST'));
    $formMethod = in_array($formMethod, ['GET', 'POST'], true) ? $formMethod : 'POST';
    $formAction = is_string($formAction) && trim($formAction) !== '' ? trim($formAction) : '/contact';
@endphp

<section
    id="quote"
    class="theme-section theme-section-quote-form bg-[#f8fafc]"
>
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-8 lg:grid-cols-[0.82fr_1fr] lg:items-start">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#ea580c] uppercase"
                >
                    {{ __('capell-theme-local-services::generic.quote_form_label') }}
                </p>
                @if ($sectionHeading)
                    <h2
                        class="mt-4 text-3xl font-black tracking-tight text-[#17211c]"
                    >
                        {{ $sectionHeading }}
                    </h2>
                @endif

                <p class="mt-4 max-w-2xl text-slate-600">
                    {{ $formBuilderAvailable ?? false ? __('capell-theme-local-services::generic.quote_form_copy') : __('capell-theme-local-services::generic.quote_form_copy_fallback') }}
                </p>
            </div>

            <form
                class="border border-[#99f6e4] bg-white p-5 shadow-sm"
                action="{{ $formAction }}"
                method="{{ $formMethod }}"
                aria-label="{{ __('capell-theme-local-services::generic.quote_form_aria_label') }}"
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                            for="local-services-quote-name"
                        >
                            {{ __('capell-theme-local-services::generic.quote_field_name') }}
                        </label>
                        <input
                            id="local-services-quote-name"
                            name="name"
                            type="text"
                            autocomplete="name"
                            required
                            class="mt-2 w-full border border-slate-200 bg-[#f8fafc] px-4 py-3 text-sm text-[#17211c] transition outline-none focus:border-[#0f766e] focus:bg-white"
                        />
                    </div>

                    <div>
                        <label
                            class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                            for="local-services-quote-phone"
                        >
                            {{ __('capell-theme-local-services::generic.quote_field_phone') }}
                        </label>
                        <input
                            id="local-services-quote-phone"
                            name="phone"
                            type="tel"
                            autocomplete="tel"
                            required
                            class="mt-2 w-full border border-slate-200 bg-[#f8fafc] px-4 py-3 text-sm text-[#17211c] transition outline-none focus:border-[#0f766e] focus:bg-white"
                        />
                    </div>

                    <div>
                        <label
                            class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                            for="local-services-quote-postcode"
                        >
                            {{ __('capell-theme-local-services::generic.quote_field_postcode') }}
                        </label>
                        <input
                            id="local-services-quote-postcode"
                            name="postcode"
                            type="text"
                            autocomplete="postal-code"
                            class="mt-2 w-full border border-slate-200 bg-[#f8fafc] px-4 py-3 text-sm text-[#17211c] transition outline-none focus:border-[#0f766e] focus:bg-white"
                        />
                    </div>

                    <div>
                        <label
                            class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                            for="local-services-quote-service"
                        >
                            {{ __('capell-theme-local-services::generic.quote_field_service') }}
                        </label>
                        <input
                            id="local-services-quote-service"
                            name="service"
                            type="text"
                            required
                            class="mt-2 w-full border border-slate-200 bg-[#f8fafc] px-4 py-3 text-sm text-[#17211c] transition outline-none focus:border-[#0f766e] focus:bg-white"
                        />
                    </div>
                </div>

                <div class="mt-4">
                    <label
                        class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                        for="local-services-quote-message"
                    >
                        {{ __('capell-theme-local-services::generic.quote_field_message') }}
                    </label>
                    <textarea
                        id="local-services-quote-message"
                        name="message"
                        rows="4"
                        class="mt-2 w-full border border-slate-200 bg-[#f8fafc] px-4 py-3 text-sm text-[#17211c] transition outline-none focus:border-[#0f766e] focus:bg-white"
                    ></textarea>
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

                <button
                    type="submit"
                    class="mt-4 w-full bg-[#0f766e] px-5 py-3 text-sm font-black text-white transition hover:bg-[#115e59] focus:ring-2 focus:ring-[#0f766e] focus:ring-offset-2 focus:outline-none"
                >
                    {{ __('capell-theme-local-services::generic.quote_submit_label') }}
                </button>
            </form>
        </div>
    </div>
</section>

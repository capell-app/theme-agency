@php
    $newsletterAvailable ??= false;
@endphp

<section
    class="theme-section theme-section-newsletter bg-[#111827] px-6 py-14 text-white"
>
    @isset($heading)
        <div
            class="mx-auto grid max-w-5xl gap-6 lg:grid-cols-[0.8fr_1fr] lg:items-center"
        >
            <div>
                <p
                    class="text-xs font-black tracking-[0.2em] text-[#fbbf24] uppercase"
                >
                    {{ __('capell-theme-knowledge::generic.newsletter_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black tracking-tight text-white">
                    {{ $heading }}
                </h2>
                <p class="mt-4 max-w-2xl text-slate-200">
                    {{ $newsletterAvailable ?? false ? __('capell-theme-knowledge::generic.newsletter_connected') : __('capell-theme-knowledge::generic.newsletter_static') }}
                </p>
            </div>

            <div class="border border-white/15 bg-white p-5 text-[#111827]">
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#1d4ed8] uppercase"
                >
                    {{ __('capell-theme-knowledge::generic.digest_label') }}
                </p>
                <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto]">
                    <div
                        class="border border-[#dbeafe] bg-[#eff6ff] px-4 py-3 text-sm font-bold text-slate-500"
                    >
                        {{ __('capell-theme-knowledge::generic.email_placeholder') }}
                    </div>
                    <div
                        class="bg-[#1d4ed8] px-5 py-3 text-sm font-black text-white"
                    >
                        {{ __('capell-theme-knowledge::generic.subscribe_action') }}
                    </div>
                </div>
            </div>
        </div>
    @endisset
</section>

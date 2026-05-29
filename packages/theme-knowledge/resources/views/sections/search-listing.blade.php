<section class="theme-section theme-section-search-listing bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <p
                class="text-xs font-black tracking-[0.18em] text-[#f59e0b] uppercase"
            >
                {{ __('capell-theme-knowledge::generic.search_label') }}
            </p>
            <h2 class="mt-4 text-4xl font-black tracking-tight text-[#111827]">
                {{ $heading }}
            </h2>
        @endisset

        <div class="mt-8 border border-[#bfdbfe] bg-[#eff6ff] p-5">
            <p class="text-sm font-bold text-slate-600">
                {{ $searchAvailable ?? false ? __('capell-theme-knowledge::generic.search_connected') : __('capell-theme-knowledge::generic.search_static') }}
            </p>
            <div class="mt-5 grid gap-3 md:grid-cols-[1fr_auto]">
                <div
                    class="border border-[#dbeafe] bg-white px-4 py-3 text-sm font-bold text-slate-500"
                >
                    {{ __('capell-theme-knowledge::generic.search_placeholder') }}
                </div>
                <div
                    class="bg-[#1d4ed8] px-5 py-3 text-sm font-black text-white"
                >
                    {{ __('capell-theme-knowledge::generic.search_action') }}
                </div>
            </div>
        </div>
    </div>
</section>

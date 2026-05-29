<section class="theme-section theme-section-authors bg-white">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 pt-14">
            <p class="text-xs font-black tracking-[0.18em] text-[#1d4ed8] uppercase">
                {{ __('capell-theme-knowledge::generic.authors_label') }}
            </p>
            <h2 class="mt-4 text-4xl font-black tracking-tight text-[#111827]">
                {{ $heading }}
            </h2>
        </div>
    @endisset

    <div
        class="mx-auto mt-6 grid max-w-5xl gap-4 px-6 pb-14 sm:grid-cols-2 lg:grid-cols-4"
    >
        <div
            class="border border-[#dbeafe] bg-[#eff6ff] p-4"
        >
            <p class="h-10 w-10 bg-[#1d4ed8]"></p>
            <p class="mt-3 font-black text-[#111827]">Editorial Team</p>
            <p class="mt-1 text-sm text-stone-600">
                Subject-matter writing and review teams.
            </p>
        </div>
        <div
            class="border border-[#dbeafe] bg-[#eff6ff] p-4"
        >
            <p class="h-10 w-10 bg-[#f59e0b]"></p>
            <p class="mt-3 font-black text-[#111827]">Design Staff</p>
            <p class="mt-1 text-sm text-stone-600">
                Visual systems and layout operators.
            </p>
        </div>
        <div
            class="border border-[#dbeafe] bg-[#eff6ff] p-4"
        >
            <p class="h-10 w-10 bg-[#1d4ed8]"></p>
            <p class="mt-3 font-black text-[#111827]">Research</p>
            <p class="mt-1 text-sm text-stone-600">
                Source analysts and fact-check workflows.
            </p>
        </div>
        <div
            class="border border-[#dbeafe] bg-[#eff6ff] p-4"
        >
            <p class="h-10 w-10 bg-[#f59e0b]"></p>
            <p class="mt-3 font-black text-[#111827]">Growth Ops</p>
            <p class="mt-1 text-sm text-stone-600">
                Optimization and analytics enablement.
            </p>
        </div>
    </div>
</section>

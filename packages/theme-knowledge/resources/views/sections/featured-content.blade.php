<section class="theme-section theme-section-featured-content">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2 class="text-4xl font-black tracking-tight text-[#4b5563]">
                {{ $heading }}
            </h2>
        </div>
    @endisset

    <div class="mx-auto grid max-w-5xl gap-4 px-6 pb-14 md:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-5">
            <p
                class="text-xs font-black tracking-widest text-[#4b5563] uppercase"
            >
                Featured
            </p>
            <h3 class="mt-2 text-lg font-black">Field Notes</h3>
            <p class="mt-2 text-sm text-stone-600">
                Curated thought pieces with practical execution details.
            </p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5">
            <p
                class="text-xs font-black tracking-widest text-[#4b5563] uppercase"
            >
                Templates
            </p>
            <h3 class="mt-2 text-lg font-black">Design Systems</h3>
            <p class="mt-2 text-sm text-stone-600">
                Download-ready snippets to speed content production.
            </p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5">
            <p
                class="text-xs font-black tracking-widest text-[#4b5563] uppercase"
            >
                Signals
            </p>
            <h3 class="mt-2 text-lg font-black">Insights</h3>
            <p class="mt-2 text-sm text-stone-600">
                Metrics and methods to improve conversion from insight pages.
            </p>
        </article>
    </div>
</section>

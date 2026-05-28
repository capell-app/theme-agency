<section class="theme-section theme-section-hero bg-[#f8fafc] px-6 py-20">
    @isset($heading)
        <div
            class="mx-auto grid max-w-5xl gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-end"
        >
            <div class="space-y-5">
                <p class="text-xs font-black tracking-[0.16em] text-slate-500">
                    Portfolio Home
                </p>
                <h2 class="text-5xl font-black tracking-tight text-[#0f172a]">
                    {{ $heading }}
                </h2>
                <p class="max-w-2xl text-lg text-stone-600">
                    Premium portfolio experiences engineered for conversion,
                    reputation, and long-form storytelling.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a
                        href="#case-studies"
                        class="inline-flex rounded-full bg-[#0f172a] px-5 py-3 text-sm font-black text-white"
                    >
                        Explore case studies
                    </a>
                    <a
                        href="#work-grid"
                        class="inline-flex rounded-full border border-[#cbd5e1] px-5 py-3 text-sm font-black text-[#0f172a]"
                    >
                        View selected work
                    </a>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-[#e2e8f0] bg-white p-5">
                    <p
                        class="text-xs font-black tracking-[0.16em] text-slate-500"
                    >
                        KPI
                    </p>
                    <p class="mt-3 text-3xl font-black">+42%</p>
                    <p class="mt-2 text-xs text-stone-600">
                        Average conversion lift
                    </p>
                </div>
                <div class="rounded-2xl border border-[#e2e8f0] bg-white p-5">
                    <p
                        class="text-xs font-black tracking-[0.16em] text-slate-500"
                    >
                        KPI
                    </p>
                    <p class="mt-3 text-3xl font-black">120+</p>
                    <p class="mt-2 text-xs text-stone-600">Campaigns shipped</p>
                </div>
                <div class="rounded-2xl border border-[#e2e8f0] bg-white p-5">
                    <p
                        class="text-xs font-black tracking-[0.16em] text-slate-500"
                    >
                        KPI
                    </p>
                    <p class="mt-3 text-3xl font-black">6h</p>
                    <p class="mt-2 text-xs text-stone-600">
                        Median launch time
                    </p>
                </div>
            </div>
        </div>
    @endisset
</section>

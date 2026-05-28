<section class="theme-section theme-section-authors">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2 class="text-4xl font-black tracking-tight text-[#4b5563]">
                {{ $heading }}
            </h2>
        </div>
    @endisset

    <div
        class="mx-auto mt-6 grid max-w-5xl gap-4 px-6 pb-14 sm:grid-cols-2 lg:grid-cols-4"
    >
        <div
            class="rounded-xl border border-slate-200 bg-white p-4 text-center"
        >
            <p class="mx-auto h-10 w-10 rounded-full bg-slate-200"></p>
            <p class="mt-3 font-semibold text-[#4b5563]">Editorial Team</p>
            <p class="mt-1 text-sm text-stone-600">
                Subject-matter writing and review teams.
            </p>
        </div>
        <div
            class="rounded-xl border border-slate-200 bg-white p-4 text-center"
        >
            <p class="mx-auto h-10 w-10 rounded-full bg-slate-200"></p>
            <p class="mt-3 font-semibold text-[#4b5563]">Design Staff</p>
            <p class="mt-1 text-sm text-stone-600">
                Visual systems and layout operators.
            </p>
        </div>
        <div
            class="rounded-xl border border-slate-200 bg-white p-4 text-center"
        >
            <p class="mx-auto h-10 w-10 rounded-full bg-slate-200"></p>
            <p class="mt-3 font-semibold text-[#4b5563]">Research</p>
            <p class="mt-1 text-sm text-stone-600">
                Source analysts and fact-check workflows.
            </p>
        </div>
        <div
            class="rounded-xl border border-slate-200 bg-white p-4 text-center"
        >
            <p class="mx-auto h-10 w-10 rounded-full bg-slate-200"></p>
            <p class="mt-3 font-semibold text-[#4b5563]">Growth Ops</p>
            <p class="mt-1 text-sm text-stone-600">
                Optimization and analytics enablement.
            </p>
        </div>
    </div>
</section>

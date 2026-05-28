<section class="theme-section theme-section-campaigns">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2 class="text-4xl font-black tracking-tight text-[#0f172a]">
                {{ $heading }}
            </h2>
        </div>
    @endisset

    <div class="mx-auto mt-6 grid max-w-5xl gap-4 px-6 pb-14 md:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-black tracking-widest text-slate-500">
                Campaigns
            </p>
            <h3 class="mt-2 text-lg font-black">Awareness</h3>
            <p class="mt-2 text-sm text-stone-600">
                {{ $campaignStudioAvailable ?? false ? __('capell-theme-nonprofit::generic.campaigns_connected') : __('capell-theme-nonprofit::generic.campaigns_static') }}
            </p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-black tracking-widest text-slate-500">
                Outreach
            </p>
            <h3 class="mt-2 text-lg font-black">Donor Path</h3>
            <p class="mt-2 text-sm text-stone-600">
                Designed to improve donor confidence and return.
            </p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-black tracking-widest text-slate-500">
                Retention
            </p>
            <h3 class="mt-2 text-lg font-black">Community</h3>
            <p class="mt-2 text-sm text-stone-600">
                Nurture flows for recurring advocacy and renewal.
            </p>
        </article>
    </div>
</section>

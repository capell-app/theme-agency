<section class="theme-section theme-section-cta bg-[#0f172a] text-white">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-16">
            <div
                class="grid gap-8 rounded-3xl border border-white/20 bg-white/5 p-8 md:grid-cols-[1.1fr_0.9fr] md:p-12"
            >
                <div>
                    <p
                        class="text-xs font-black tracking-[0.16em] text-slate-300"
                    >
                        Final action
                    </p>
                    <h2
                        class="mt-3 text-4xl font-black tracking-tight text-white"
                    >
                        {{ $heading }}
                    </h2>
                    <p class="mt-4 max-w-2xl text-slate-300">
                        Start with a premium portfolio review and leave with a
                        rollout plan that feels intentional, complete, and
                        premium.
                    </p>
                </div>
                <div class="flex items-end">
                    <a
                        href="#"
                        class="inline-flex rounded-full bg-white px-5 py-3 text-sm font-black text-[#0f172a]"
                    >
                        Book a strategy call
                    </a>
                </div>
            </div>
        </div>
    @endisset
</section>

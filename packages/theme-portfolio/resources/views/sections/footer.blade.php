<footer
    class="theme-section theme-section-footer border-t border-slate-200 bg-[#0f172a] text-white"
>
    <div
        class="mx-auto grid max-w-5xl gap-6 px-6 py-12 md:grid-cols-[1fr_1fr_1fr]"
    >
        <div>
            <p
                class="text-xs font-black tracking-[0.16em] text-slate-300 uppercase"
            >
                {{ $heading ?? 'Creative Direction' }}
            </p>
            <p class="mt-3 max-w-sm text-sm text-slate-300">
                Build trust with premium portfolio structure, sharp messaging,
                and measurable outcomes.
            </p>
            <p class="mt-4 text-xs text-slate-400">© {{ now()->year }}</p>
        </div>

        <div>
            <h3 class="text-sm font-black">Explore</h3>
            <ul class="mt-3 space-y-2 text-sm text-slate-300">
                <li>
                    <a class="hover:text-white" href="#case-studies">
                        Case studies
                    </a>
                </li>
                <li><a class="hover:text-white" href="#work-grid">Work</a></li>
                <li><a class="hover:text-white" href="#">Services</a></li>
            </ul>
        </div>

        <div>
            <h3 class="text-sm font-black">Contact</h3>
            <p class="mt-3 text-sm text-slate-300">studio@portfolio.example</p>
            <a
                href="#"
                class="mt-3 inline-flex rounded-full border border-slate-400 px-4 py-2 text-xs font-black"
            >
                Request media kit
            </a>
        </div>
    </div>
</footer>

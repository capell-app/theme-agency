<footer
    class="theme-section theme-section-footer portfolio-bg-ink border-t border-slate-200 text-white"
>
    <div
        class="mx-auto grid max-w-5xl gap-8 px-6 py-12 md:grid-cols-[1.2fr_0.8fr_0.8fr]"
    >
        <div>
            <p
                class="text-xs font-black tracking-[0.16em] text-slate-300 uppercase"
            >
                {{ $brandName ?? $heading ?? __('capell-theme-portfolio::generic.footer_heading') }}
            </p>
            <p class="mt-3 max-w-sm text-sm text-slate-300">
                {{ $summary ?? __('capell-theme-portfolio::generic.footer_summary') }}
            </p>
            <p class="mt-4 text-xs text-slate-400">© {{ now()->year }}</p>
        </div>

        @foreach (($columns ?? []) as $column)
            <div>
                <h3 class="text-sm font-black">
                    {{ $column['heading'] ?? __('capell-theme-portfolio::generic.footer_heading') }}
                </h3>
                <ul class="mt-3 space-y-2 text-sm text-slate-300">
                    @foreach (($column['links'] ?? []) as $link)
                        <li>
                            <a
                                class="hover:text-white"
                                href="{{ $link['url'] ?? '#' }}"
                            >
                                {{ $link['label'] ?? '' }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        <div>
            <h3 class="text-sm font-black">
                {{ __('capell-theme-portfolio::generic.contact_label') }}
            </h3>
            <p class="mt-3 text-sm text-slate-300">
                {{ __('capell-theme-portfolio::generic.contact_email') }}
            </p>
            <a
                href="#"
                class="mt-3 inline-flex rounded-full border border-slate-400 px-4 py-2 text-xs font-black"
            >
                {{ __('capell-theme-portfolio::generic.media_kit_label') }}
            </a>
        </div>
    </div>
</footer>

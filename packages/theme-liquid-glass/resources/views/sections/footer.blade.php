<footer class="theme-footer liquid-glass-footer px-5 py-12 sm:px-6 lg:px-8">
    <h2 class="sr-only">
        {{ __('capell-theme-liquid-glass::generic.footer') }}
    </h2>

    <div class="mx-auto grid max-w-7xl gap-8 md:grid-cols-[1fr_2fr]">
        <div>
            <p class="text-2xl font-black">{{ $section->brandName }}</p>
            @if ($section->summary)
                <p
                    class="mt-3 max-w-sm text-sm leading-7 text-[color-mix(in_srgb,var(--liquid-glass-foreground)_68%,transparent)]"
                >
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="grid gap-6 sm:grid-cols-3">
            @foreach ($section->columns as $column)
                <div class="liquid-glass-footer-column">
                    <h3 class="text-sm font-bold text-[var(--theme-primary)]">
                        {{ $column['heading'] }}
                    </h3>
                    <ul
                        class="mt-3 space-y-2 text-sm text-[color-mix(in_srgb,var(--liquid-glass-foreground)_70%,transparent)]"
                    >
                        @foreach ($column['links'] as $link)
                            <li>
                                <a
                                    href="{{ $link['url'] }}"
                                    class="hover:text-[var(--liquid-glass-foreground)]"
                                >
                                    {{ $link['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</footer>

<footer
    class="retail-footer border-t border-stone-200 bg-[var(--retail-surface)]"
>
    <h2 class="sr-only">{{ __('capell-theme-commerce::generic.footer') }}</h2>
    <div class="grid gap-10 px-6 py-14 md:grid-cols-[1fr_2fr]">
        <div>
            <p class="text-lg font-black text-[var(--retail-ink)]">
                {{ $section->brandName }}
            </p>
            @if ($section->summary ?? null)
                <p class="mt-3 max-w-sm text-sm">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="grid gap-7 sm:grid-cols-3">
            @foreach (($section->columns ?? []) as $column)
                <div>
                    <h3 class="text-sm font-black">
                        {{ $column['heading'] }}
                    </h3>
                    <ul
                        class="mt-4 space-y-2 text-sm font-semibold text-stone-600"
                    >
                        @foreach ($column['links'] as $link)
                            <li>
                                <a
                                    href="{{ $link['url'] }}"
                                    class="hover:text-[var(--retail-primary)]"
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

<footer class="saas-footer border-t border-slate-200 bg-slate-50">
    <h2 class="sr-only">{{ __('capell-theme-saas::generic.footer') }}</h2>
    <div class="grid gap-10 px-6 py-14 md:grid-cols-[1fr_2fr]">
        <div>
            <p class="text-lg font-black text-slate-950">
                {{ $section->brandName }}
            </p>
            @if ($section->summary)
                <p class="mt-3 max-w-sm text-sm text-slate-700">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="grid gap-7 sm:grid-cols-3">
            @foreach ($section->columns as $column)
                <div>
                    <h3 class="text-sm font-black text-slate-950">
                        {{ $column['heading'] }}
                    </h3>
                    <ul
                        class="mt-4 space-y-2 text-sm font-semibold text-slate-600"
                    >
                        @foreach ($column['links'] as $link)
                            <li>
                                <a
                                    href="{{ $link['url'] }}"
                                    class="hover:text-blue-700"
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

<footer class="bg-slate-950 text-white">
    <h2 class="sr-only">
        {{ __('capell-theme-business-solutions::generic.footer') }}
    </h2>
    <div
        class="business-theme-container grid gap-8 px-5 py-12 md:grid-cols-[1fr_2fr]"
    >
        <div>
            <p class="text-xl font-bold">{{ $section->brandName }}</p>
            @if ($section->summary)
                <p class="mt-3 max-w-sm text-sm leading-6 text-white/60">
                    {{ $section->summary }}
                </p>
            @endif
        </div>
        <div class="grid gap-6 sm:grid-cols-3">
            @foreach ($section->columns as $column)
                <div>
                    <h3 class="text-sm font-bold text-white">
                        {{ $column['heading'] }}
                    </h3>
                    <ul class="text-white/62 mt-3 space-y-2 text-sm">
                        @foreach ($column['links'] as $link)
                            <li>
                                <a
                                    href="{{ $link['url'] }}"
                                    class="hover:text-white"
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

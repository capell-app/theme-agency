<section class="bg-white">
    <div class="business-theme-container">
        <div
            class="business-theme-card grid gap-6 p-6 sm:p-8 lg:grid-cols-[0.65fr_0.35fr] lg:items-center"
        >
            <div>
                <p
                    class="mb-3 text-sm font-bold uppercase tracking-[0.16em] text-[var(--theme-primary)]"
                >
                    {{ __('capell-theme-business-solutions::generic.cta') }}
                </p>
                <h2 class="text-3xl font-bold leading-tight sm:text-4xl">
                    {{ $section->heading }}
                </h2>
                @if ($section->summary)
                    <p
                        class="mt-4 max-w-2xl text-base leading-7 text-slate-600"
                    >
                        {{ $section->summary }}
                    </p>
                @endif
            </div>
            <div class="flex flex-wrap gap-3 lg:justify-end">
                @foreach ($section->actions as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="business-theme-button"
                    >
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>

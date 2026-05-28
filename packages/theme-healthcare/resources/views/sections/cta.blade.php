<section class="healthcare-final-cta bg-white">
    <div class="px-6">
        <div
            class="rounded-xl border border-[#0f766e]/15 bg-[#0f766e] p-8 text-white md:p-12"
        >
            <div class="grid gap-8 md:grid-cols-[1fr_auto] md:items-center">
                <div>
                    <h2
                        class="text-3xl font-black text-white sm:text-4xl md:text-5xl"
                    >
                        {{ $section->heading }}
                    </h2>
                    @if ($section->summary ?? null)
                        <p class="mt-4 max-w-2xl text-stone-100">
                            {{ $section->summary }}
                        </p>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3 md:justify-end">
                    @foreach (($section->actions ?? []) as $action)
                        <a
                            href="{{ $action['url'] }}"
                            class="healthcare-cta {{ ($action['style'] ?? 'primary') === 'secondary' ? 'border border-white/40 text-white' : 'bg-white text-[#0f766e]' }}"
                        >
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

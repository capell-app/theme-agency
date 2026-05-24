<section class="retail-final-cta bg-white">
    <div class="px-6">
        <div
            class="rounded-xl border border-[#1f5f4a]/15 bg-[#1f5f4a] p-8 text-white md:p-12"
        >
            <div class="grid gap-8 md:grid-cols-[1fr_auto] md:items-center">
                <div>
                    <h2 class="text-white">{{ $section->heading }}</h2>
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
                            class="retail-cta {{ ($action['style'] ?? 'primary') === 'secondary' ? 'border border-white/40 text-white' : 'bg-white text-[#1f5f4a]' }}"
                        >
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

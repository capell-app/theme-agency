<section class="saas-final-cta bg-white">
    <div class="px-6">
        <div
            class="rounded-3xl border border-blue-100 bg-blue-600 p-8 text-white md:p-12"
        >
            <div class="grid gap-8 md:grid-cols-[1fr_auto] md:items-center">
                <div>
                    <h2
                        class="text-3xl font-black text-white sm:text-4xl md:text-5xl"
                    >
                        {{ $section->heading }}
                    </h2>
                    @if ($section->summary)
                        <p class="mt-4 max-w-2xl text-blue-50">
                            {{ $section->summary }}
                        </p>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3 md:justify-end">
                    @foreach ($section->actions as $action)
                        <a
                            href="{{ $action['url'] }}"
                            class="saas-cta {{ ($action['style'] ?? 'primary') === 'secondary' ? 'border border-white/40 text-white' : 'bg-white text-blue-700' }}"
                        >
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

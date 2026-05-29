@php
    $heading = $section->heading ?? $heading ?? null;
    $summary = $section->summary ?? $summary ?? null;
    $actions = $section->actions ?? [];
@endphp

<section class="theme-section theme-section-cta bg-[#0f172a] text-white">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-16">
            <div
                class="grid gap-8 border border-white/20 bg-white/5 p-8 shadow-2xl shadow-black/20 md:grid-cols-[1.1fr_0.9fr] md:p-12"
            >
                <div>
                    <p
                        class="text-xs font-black tracking-[0.16em] text-[#fb923c] uppercase"
                    >
                        {{ __('capell-theme-portfolio::generic.final_action_label') }}
                    </p>
                    <h2
                        class="mt-3 text-4xl font-black tracking-tight text-white"
                    >
                        {{ $heading }}
                    </h2>
                    @if ($summary)
                        <p class="mt-4 max-w-2xl text-slate-300">
                            {{ $summary }}
                        </p>
                    @endif
                </div>
                <div class="flex flex-col justify-end gap-4">
                    <div class="grid gap-2" aria-hidden="true">
                        <span class="h-3 w-32 rounded-full bg-white/80"></span>
                        <span class="h-3 w-48 rounded-full bg-white/30"></span>
                        <span class="h-3 w-40 rounded-full bg-[#fb923c]"></span>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @forelse ($actions as $action)
                            <a
                                href="{{ $action['url'] ?? '#' }}"
                                class="{{ ($action['style'] ?? 'primary') === 'secondary' ? 'border border-white/25 bg-transparent text-white' : 'bg-white text-[#0f172a]' }} inline-flex rounded-full px-5 py-3 text-sm font-black"
                            >
                                {{ $action['label'] ?? __('capell-theme-portfolio::generic.view_case_label') }}
                            </a>
                        @empty
                            <a
                                href="#"
                                class="inline-flex rounded-full bg-white px-5 py-3 text-sm font-black text-[#0f172a]"
                            >
                                {{ __('capell-theme-portfolio::generic.book_call_label') }}
                            </a>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endisset
</section>

@php
    $heading = $section->heading ?? $heading ?? null;
    $summary = $section->summary ?? $summary ?? null;
    $actions = $section->actions ?? [];
@endphp

<section class="theme-section theme-section-cta portfolio-bg-ink text-white">
    @isset($heading)
        <div class="mx-auto max-w-6xl px-6 py-16">
            <div
                class="grid gap-8 border border-white/20 bg-white/5 p-8 shadow-2xl shadow-black/20 md:grid-cols-[1fr_1fr] md:p-12"
            >
                <div>
                    <p
                        class="portfolio-text-highlight text-xs font-black uppercase"
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

                <div class="grid gap-5">
                    <div
                        class="portfolio-bg-deep grid gap-3 p-5"
                        aria-hidden="true"
                    >
                        <div class="grid grid-cols-3 gap-3">
                            <span
                                class="bg-white/10 p-3 text-xs font-black text-white/70 uppercase"
                            >
                                {{ __('capell-theme-portfolio::generic.brief_label') }}
                            </span>
                            <span
                                class="bg-white/10 p-3 text-xs font-black text-white/70 uppercase"
                            >
                                {{ __('capell-theme-portfolio::generic.proof_signal') }}
                            </span>
                            <span
                                class="bg-white/10 p-3 text-xs font-black text-white/70 uppercase"
                            >
                                {{ __('capell-theme-portfolio::generic.publish_label') }}
                            </span>
                        </div>
                        <span class="block h-2 w-3/4 bg-white/70"></span>
                        <span
                            class="portfolio-bg-highlight block h-2 w-1/2"
                        ></span>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @forelse ($actions as $action)
                            <a
                                href="{{ $action['url'] ?? '#' }}"
                                class="{{ ($action['style'] ?? 'primary') === 'secondary' ? 'border border-white/25 bg-transparent text-white' : 'portfolio-text-ink bg-white' }} inline-flex px-5 py-3 text-sm font-black"
                            >
                                {{ $action['label'] ?? __('capell-theme-portfolio::generic.view_case_label') }}
                            </a>
                        @empty
                            <a
                                href="#"
                                class="portfolio-text-ink inline-flex bg-white px-5 py-3 text-sm font-black"
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

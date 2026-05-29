<section
    class="theme-cta border-b border-slate-200/80 bg-white dark:border-white/10 dark:bg-slate-900"
>
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-12 lg:py-16">
        <div
            class="grid gap-6 border-y border-slate-200 py-7 sm:py-9 lg:grid-cols-[0.85fr_1.15fr] lg:items-center dark:border-white/10"
        >
            <div>
                <p
                    class="mb-3 text-xs font-semibold tracking-[0.16em] text-[var(--theme-primary)] uppercase dark:text-[var(--theme-accent)]"
                >
                    {{ __('capell-theme-corporate::generic.board_action_label') }}
                </p>
                <h2
                    class="max-w-xl text-2xl leading-tight font-semibold text-slate-950 sm:text-3xl lg:text-4xl dark:text-white"
                >
                    {{ $section->heading }}
                </h2>
            </div>
            <div>
                @if ($section->summary)
                    <p
                        class="max-w-2xl text-sm leading-6 text-slate-600 sm:leading-7 dark:text-slate-300"
                    >
                        {{ $section->summary }}
                    </p>
                @endif

                <div class="mt-5 grid gap-2 sm:grid-cols-3">
                    @foreach ([__('capell-theme-corporate::generic.cta_step_brief'), __('capell-theme-corporate::generic.cta_step_review'), __('capell-theme-corporate::generic.cta_step_decide')] as $step)
                        <div
                            class="border border-slate-200 bg-[#f7f8f6] px-3 py-3 dark:border-white/10 dark:bg-white/[0.03]"
                        >
                            <p
                                class="text-xs font-semibold tracking-[0.14em] text-slate-600 uppercase dark:text-slate-300"
                            >
                                {{ $step }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 flex flex-wrap gap-2 sm:mt-6 sm:gap-3">
                    @foreach ($section->actions as $action)
                        <a
                            href="{{ $action['url'] }}"
                            class="bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[var(--theme-primary)] sm:px-5 sm:py-3 dark:bg-white dark:text-slate-950 dark:hover:bg-[var(--theme-accent)]"
                        >
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

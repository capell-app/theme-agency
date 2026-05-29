<section class="theme-cta px-6 py-20">
    <div
        class="mx-auto grid max-w-7xl gap-8 rounded-[2rem] bg-gradient-to-br from-[var(--theme-primary)] via-fuchsia-500 to-[var(--theme-accent)] p-10 text-white lg:grid-cols-[0.85fr_1.15fr] lg:items-center"
    >
        <div>
            <p
                class="text-sm font-black tracking-[0.24em] text-white/80 uppercase"
            >
                {{ __('capell-theme-agency::generic.launch_room') }}
            </p>
            <h2 class="mt-4 max-w-3xl text-5xl font-black tracking-tight">
                {{ $section->heading }}
            </h2>
            @if ($section->summary)
                <p class="mt-4 max-w-2xl text-white/80">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="rounded-[1.5rem] bg-zinc-950/90 p-5 shadow-2xl">
            <div class="grid gap-3 sm:grid-cols-3">
                @foreach ([__('capell-theme-agency::generic.cta_step_brief'), __('capell-theme-agency::generic.cta_step_make'), __('capell-theme-agency::generic.cta_step_launch')] as $step)
                    <div class="rounded-2xl bg-white/10 p-4">
                        <p
                            class="text-xs font-black tracking-[0.18em] text-white uppercase"
                        >
                            {{ $step }}
                        </p>
                        <span
                            class="mt-5 block h-2 rounded-full bg-white"
                            aria-hidden="true"
                        ></span>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                @foreach ($section->actions as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="rounded-full bg-white px-6 py-3 text-sm font-bold text-zinc-950 transition hover:bg-zinc-100"
                    >
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>

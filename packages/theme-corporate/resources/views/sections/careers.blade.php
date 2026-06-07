@php
    $roles = $section->items ?? $section->roles ?? [];
    $benefits = $section->benefits ?? $section->perks ?? [];
    $heading = $section->heading ?? __('capell-theme-corporate::generic.careers_heading');
    $summary = $section->summary ?? __('capell-theme-corporate::generic.careers_summary');
@endphp

<section
    class="theme-careers border-b border-slate-200/80 bg-white dark:border-white/10 dark:bg-slate-900"
>
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-12 lg:py-16">
        <div class="grid gap-6 lg:grid-cols-[0.7fr_1.3fr] lg:gap-8">
            <div>
                <p
                    class="mb-3 text-xs font-semibold tracking-[0.16em] text-[var(--theme-primary)] uppercase dark:text-[var(--theme-accent)]"
                >
                    {{ __('capell-theme-corporate::generic.careers_eyebrow') }}
                </p>
                <h2
                    class="max-w-lg text-2xl leading-tight font-semibold text-slate-950 sm:text-3xl lg:text-4xl dark:text-white"
                >
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p
                        class="mt-3 max-w-md text-sm leading-6 text-slate-600 sm:leading-7 dark:text-slate-300"
                    >
                        {{ $summary }}
                    </p>
                @endif

                @if ($benefits !== [])
                    <div class="mt-6 grid max-w-md gap-2 sm:grid-cols-2">
                        @foreach (array_slice($benefits, 0, 4) as $benefit)
                            <p
                                class="corporate-card-muted px-3 py-3 text-xs font-semibold tracking-[0.12em] text-slate-600 uppercase dark:bg-white/[0.03] dark:text-slate-300"
                            >
                                {{ is_array($benefit) ? $benefit['label'] ?? $benefit['title'] ?? '' : $benefit }}
                            </p>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="grid gap-3">
                @forelse ($roles as $role)
                    <article
                        class="corporate-card grid gap-4 p-4 sm:grid-cols-[1fr_0.42fr] sm:p-5 dark:bg-white/[0.03]"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold tracking-[0.14em] text-[var(--theme-primary)] uppercase dark:text-[var(--theme-accent)]"
                            >
                                {{ $role['team'] ?? $role['department'] ?? __('capell-theme-corporate::generic.careers_role_label') }}
                            </p>
                            <h3
                                class="mt-2 text-lg font-semibold text-slate-950 dark:text-white"
                            >
                                {{ $role['title'] ?? $role['name'] ?? '' }}
                            </h3>
                            @if (! empty($role['summary']))
                                <p
                                    class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300"
                                >
                                    {{ $role['summary'] }}
                                </p>
                            @endif
                        </div>

                        <dl
                            class="grid gap-2 text-sm text-slate-700 dark:text-slate-200"
                        >
                            @if (! empty($role['location']))
                                <div>
                                    <dt
                                        class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase dark:text-slate-400"
                                    >
                                        {{ __('capell-theme-corporate::generic.careers_location_label') }}
                                    </dt>
                                    <dd class="mt-1">
                                        {{ $role['location'] }}
                                    </dd>
                                </div>
                            @endif

                            @if (! empty($role['type']))
                                <div>
                                    <dt
                                        class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase dark:text-slate-400"
                                    >
                                        {{ __('capell-theme-corporate::generic.careers_type_label') }}
                                    </dt>
                                    <dd class="mt-1">{{ $role['type'] }}</dd>
                                </div>
                            @endif

                            @if (! empty($role['url']))
                                <div>
                                    <a
                                        href="{{ $role['url'] }}"
                                        class="inline-flex rounded-full bg-slate-950 px-4 py-2 text-xs font-semibold text-white transition hover:bg-[var(--theme-primary)] dark:bg-white dark:text-slate-950 dark:hover:bg-[var(--theme-accent)]"
                                    >
                                        {{ $role['ctaLabel'] ?? __('capell-theme-corporate::generic.careers_apply_label') }}
                                    </a>
                                </div>
                            @endif
                        </dl>
                    </article>
                @empty
                    <div class="corporate-card-muted p-5 sm:p-6">
                        <h3
                            class="text-lg font-semibold text-slate-950 dark:text-white"
                        >
                            {{ __('capell-theme-corporate::generic.careers_empty_title') }}
                        </h3>
                        <p
                            class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300"
                        >
                            {{ __('capell-theme-corporate::generic.careers_empty_summary') }}
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

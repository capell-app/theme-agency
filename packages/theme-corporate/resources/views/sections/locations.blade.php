@php
    $items = $section->items ?? $section->locations ?? [];
    $heading = $section->heading ?? __('capell-theme-corporate::generic.locations_heading');
    $summary = $section->summary ?? __('capell-theme-corporate::generic.locations_default_summary');
@endphp

<section
    class="theme-locations border-b border-slate-200/80 bg-white dark:border-white/10 dark:bg-slate-900"
>
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-12 lg:py-16">
        <div class="grid gap-6 lg:grid-cols-[0.7fr_1.3fr] lg:gap-8">
            <div>
                <p
                    class="mb-3 text-xs font-semibold tracking-[0.16em] text-[var(--theme-primary)] uppercase dark:text-[var(--theme-accent)]"
                >
                    {{ __('capell-theme-corporate::generic.locations_eyebrow') }}
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

                <div
                    class="mt-6 grid max-w-md grid-cols-3 border border-slate-200 text-xs font-semibold text-slate-600 dark:border-white/10 dark:text-slate-300"
                >
                    <span
                        class="border-r border-slate-200 p-3 dark:border-white/10"
                    >
                        {{ __('capell-theme-corporate::generic.locations_primary_label') }}
                    </span>
                    <span
                        class="border-r border-slate-200 p-3 dark:border-white/10"
                    >
                        {{ __('capell-theme-corporate::generic.assurance_label') }}
                    </span>
                    <span class="p-3">
                        {{ __('capell-theme-corporate::generic.delivery_signal') }}
                    </span>
                </div>
            </div>

            @if ($items !== [])
                <div class="grid gap-3 md:grid-cols-2 lg:gap-4">
                    @foreach ($items as $item)
                        @php
                            $addressLines = $item['addressLines'] ?? $item['address'] ?? [];

                            if (is_string($addressLines)) {
                                $addressLines = array_filter(array_map('trim', explode("\n", $addressLines)));
                            }

                            $hours = $item['hours'] ?? $item['openingHours'] ?? [];

                            if (is_string($hours)) {
                                $hours = array_filter(array_map('trim', explode("\n", $hours)));
                            }
                        @endphp

                        <article
                            class="{{ $loop->first ? 'md:col-span-2 md:grid md:grid-cols-[0.8fr_1.2fr]' : '' }} corporate-card-muted overflow-hidden p-0 dark:bg-white/[0.03]"
                        >
                            <div
                                class="corporate-card p-4 sm:p-5 lg:p-6 dark:bg-transparent"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div>
                                        <p
                                            class="text-xs font-semibold tracking-[0.14em] text-[var(--theme-primary)] uppercase dark:text-[var(--theme-accent)]"
                                        >
                                            {{ $item['type'] ?? __('capell-theme-corporate::generic.locations_primary_label') }}
                                        </p>
                                        <h3
                                            class="mt-3 text-lg font-semibold text-slate-950 sm:text-xl dark:text-white"
                                        >
                                            {{ $item['title'] ?? $item['name'] ?? '' }}
                                        </h3>
                                    </div>

                                    <span
                                        class="font-mono text-sm font-semibold text-slate-500 dark:text-slate-400"
                                    >
                                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>

                                @if (! empty($item['summary']))
                                    <p
                                        class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ $item['summary'] }}
                                    </p>
                                @endif
                            </div>

                            <dl
                                class="grid gap-px bg-slate-200 text-sm dark:bg-white/10"
                            >
                                @if ($addressLines !== [])
                                    <div class="bg-white p-4 dark:bg-slate-900">
                                        <dt
                                            class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase dark:text-slate-400"
                                        >
                                            {{ __('capell-theme-corporate::generic.locations_address_label') }}
                                        </dt>
                                        <dd
                                            class="mt-2 leading-6 text-slate-700 dark:text-slate-200"
                                        >
                                            @foreach ($addressLines as $line)
                                                <span class="block">
                                                    {{ $line }}
                                                </span>
                                            @endforeach
                                        </dd>
                                    </div>
                                @endif

                                @if (! empty($item['phone']))
                                    <div class="bg-white p-4 dark:bg-slate-900">
                                        <dt
                                            class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase dark:text-slate-400"
                                        >
                                            {{ __('capell-theme-corporate::generic.locations_phone_label') }}
                                        </dt>
                                        <dd
                                            class="mt-2 text-slate-700 dark:text-slate-200"
                                        >
                                            {{ $item['phone'] }}
                                        </dd>
                                    </div>
                                @endif

                                @if (! empty($item['email']))
                                    <div class="bg-white p-4 dark:bg-slate-900">
                                        <dt
                                            class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase dark:text-slate-400"
                                        >
                                            {{ __('capell-theme-corporate::generic.locations_email_label') }}
                                        </dt>
                                        <dd
                                            class="mt-2 text-slate-700 dark:text-slate-200"
                                        >
                                            {{ $item['email'] }}
                                        </dd>
                                    </div>
                                @endif

                                @if ($hours !== [])
                                    <div class="bg-white p-4 dark:bg-slate-900">
                                        <dt
                                            class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase dark:text-slate-400"
                                        >
                                            {{ __('capell-theme-corporate::generic.locations_hours_label') }}
                                        </dt>
                                        <dd
                                            class="mt-2 leading-6 text-slate-700 dark:text-slate-200"
                                        >
                                            @foreach ($hours as $line)
                                                <span class="block">
                                                    {{ $line }}
                                                </span>
                                            @endforeach
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="corporate-card-muted p-5 sm:p-6">
                    <p
                        class="text-xs font-semibold tracking-[0.16em] text-[var(--theme-primary)] uppercase dark:text-[var(--theme-accent)]"
                    >
                        {{ __('capell-theme-corporate::generic.locations_empty_title') }}
                    </p>
                    <p
                        class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300"
                    >
                        {{ __('capell-theme-corporate::generic.locations_empty_summary') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</section>

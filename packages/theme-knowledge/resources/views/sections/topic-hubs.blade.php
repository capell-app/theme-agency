@php
    $sectionHeading = $heading ?? ($section->heading ?? null);
    $topicHubCards = $section->items ?? $items ?? __('capell-theme-knowledge::generic.topic_hub_cards');
    $topicHubCards = is_array($topicHubCards) ? $topicHubCards : [];
@endphp

<section class="theme-section theme-section-topic-hubs bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @if ($sectionHeading)
            <p
                class="text-xs font-black tracking-[0.18em] text-[var(--site-theme-accent)] uppercase"
            >
                {{ __('capell-theme-knowledge::generic.topic_hubs_label') }}
            </p>
            <h2
                class="mt-4 text-4xl font-black tracking-tight text-[var(--site-theme-foreground)]"
            >
                {{ $sectionHeading }}
            </h2>
        @endif

        <div class="mt-8 grid gap-4 md:grid-cols-4">
            @foreach ($topicHubCards as $topicHubCard)
                @php
                    $topicTitle = is_scalar($topicHubCard) ? (string) $topicHubCard : '';
                    $topicTitle = is_array($topicHubCard) && is_scalar($topicHubCard['title'] ?? $topicHubCard['name'] ?? null) ? (string) ($topicHubCard['title'] ?? $topicHubCard['name']) : $topicTitle;
                    $topicSummary = is_array($topicHubCard) && is_scalar($topicHubCard['summary'] ?? $topicHubCard['description'] ?? null) ? (string) ($topicHubCard['summary'] ?? $topicHubCard['description']) : '';
                @endphp

                @if ($topicTitle !== '')
                    <article
                        class="border border-[var(--site-theme-primary-border)] bg-[var(--site-theme-primary-panel)] p-5"
                    >
                        <p
                            class="font-mono text-xs font-black text-[var(--site-theme-primary)]"
                        >
                            {{ __('capell-theme-knowledge::generic.topic_signal') }}
                        </p>
                        <h3
                            class="mt-3 text-lg font-black text-[var(--site-theme-foreground)]"
                        >
                            {{ $topicTitle }}
                        </h3>
                        @if ($topicSummary !== '')
                            <p class="mt-3 text-sm leading-6 text-stone-600">
                                {{ $topicSummary }}
                            </p>
                        @endif

                        <span
                            class="mt-5 block h-1 bg-[var(--site-theme-accent)]"
                            aria-hidden="true"
                        ></span>
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</section>

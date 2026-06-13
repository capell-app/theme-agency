@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-estate-agents::generic.agents_heading'));
    $items = $section->items ?? ($items ?? []);

    if (! is_array($items) || $items === []) {
        $items = [
            ['title' => __('capell-theme-estate-agents::generic.agent_one'), 'summary' => __('capell-theme-estate-agents::generic.agent_one_summary'), 'meta' => 'Sales lead'],
            ['title' => __('capell-theme-estate-agents::generic.agent_two'), 'summary' => __('capell-theme-estate-agents::generic.agent_two_summary'), 'meta' => 'Lettings lead'],
            ['title' => __('capell-theme-estate-agents::generic.agent_three'), 'summary' => __('capell-theme-estate-agents::generic.agent_three_summary'), 'meta' => 'Valuations'],
        ];
    }
@endphp

<section class="theme-section px-6 py-16">
    <div class="mx-auto max-w-6xl">
        <p class="estate-eyebrow">
            {{ __('capell-theme-estate-agents::generic.agents_label') }}
        </p>
        <h2 class="mt-4 max-w-3xl text-4xl leading-tight font-black">
            {{ $heading }}
        </h2>
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach ($items as $agent)
                <article class="estate-agent-card">
                    <div
                        class="estate-agent-avatar"
                        aria-hidden="true"
                    ></div>
                    <p
                        class="mt-5 text-xs font-black text-[var(--estate-muted)] uppercase"
                    >
                        {{ $agent['meta'] ?? '' }}
                    </p>
                    <h3 class="mt-2 text-xl font-black">
                        {{ $agent['title'] ?? '' }}
                    </h3>
                    <p
                        class="mt-3 text-sm leading-6 text-[var(--estate-muted)]"
                    >
                        {{ $agent['summary'] ?? '' }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>

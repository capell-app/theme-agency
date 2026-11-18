@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;

    $heading = data_get($section, 'heading');
    $actions = collect(data_get($section, 'actions', []));

    if ($actions->isEmpty()) {
        $actions = collect([['label' => data_get($section, 'label'), 'url' => data_get($section, 'url')]]);
    }

    $actions = $actions
        ->map(fn (mixed $action): array => [
            'label' => data_get($action, 'label'),
            'url' => PublicUrlSanitizer::sanitize(data_get($action, 'url', data_get($action, 'href'))),
            'style' => data_get($action, 'style'),
        ])
        ->filter(fn (array $action): bool => filled($action['label']) && filled($action['url']))
        ->values();
@endphp

@if (filled($heading) || $actions->isNotEmpty())
    <section class="ppc-section ppc-section-dark">
        <div class="ppc-section-inner">
            @if (filled(data_get($section, 'kicker')))
                <p class="ppc-kicker">{{ data_get($section, 'kicker') }}</p>
            @endif

            @if (filled($heading))
                <h2>{{ $heading }}</h2>
            @endif

            @if (filled(data_get($section, 'summary')))
                <p class="ppc-lede">{{ data_get($section, 'summary') }}</p>
            @endif

            @if ($actions->isNotEmpty())
                <div class="ppc-actions">
                    @foreach ($actions as $action)
                        <a
                            class="ppc-button {{ $action['style'] === 'secondary' ? 'ppc-button-secondary' : '' }}"
                            href="{{ $action['url'] }}"
                        >
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif

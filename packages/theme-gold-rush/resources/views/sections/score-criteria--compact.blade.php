@php
    $heading = data_get($section, 'heading', __('capell-theme-gold-rush::sections.criteria.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-gold-rush::sections.criteria.summary'));
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();

    $defaultWeights = [40, 30, 20, 10];
@endphp

<section
    id="score-criteria"
    class="sbs-section"
>
    <div class="sbs-section-inner">
        <p class="sbs-kicker">
            {{ __('capell-theme-gold-rush::sections.criteria.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="sbs-lede">{{ $summary }}</p>

        <table class="sbs-criteria-table">
            <thead>
                <tr>
                    <th scope="col">
                        {{ __('capell-theme-gold-rush::sections.criteria.column_criterion') }}
                    </th>
                    <th scope="col">
                        {{ __('capell-theme-gold-rush::sections.criteria.weight_label') }}
                    </th>
                    <th scope="col">
                        {{ __('capell-theme-gold-rush::sections.criteria.column_notes') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    @php
                        $weight = data_get($item, 'weight', $defaultWeights[$loop->index] ?? 25);
                    @endphp

                    <tr>
                        <th scope="row">
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </th>
                        <td class="sbs-criteria-table-weight">
                            {{ $weight }}%
                        </td>
                        <td>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

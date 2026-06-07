@php
    $columns = $section->columns ?? $section->items ?? $section->features ?? [];
    $criteria = collect($section->criteria ?? [])
        ->map(static fn (mixed $criterion): array => is_array($criterion) ? $criterion : ['label' => (string) $criterion])
        ->values();

    if ($criteria->isEmpty()) {
        $criteria = collect($columns)
            ->flatMap(static fn (array $column): array => $column['specs'] ?? $column['criteria'] ?? [])
            ->map(static fn (mixed $criterion): array => is_array($criterion) ? $criterion : ['label' => (string) $criterion])
            ->filter(static fn (array $criterion): bool => ($criterion['label'] ?? '') !== '')
            ->unique('label')
            ->values();
    }

    if ($criteria->isEmpty()) {
        $criteria = collect([
            ['label' => __('capell-theme-commerce::generic.comparison_summary_label'), 'key' => 'summary'],
        ]);
    }

    $comparisonValue = static function (array $column, array $criterion): string {
        $key = $criterion['key'] ?? null;

        if (is_string($key) && isset($column[$key]) && is_scalar($column[$key])) {
            return (string) $column[$key];
        }

        foreach (($column['specs'] ?? $column['criteria'] ?? []) as $spec) {
            if (! is_array($spec)) {
                continue;
            }

            if (($spec['label'] ?? null) === ($criterion['label'] ?? null)) {
                return (string) ($spec['value'] ?? $spec['summary'] ?? '');
            }
        }

        return '';
    };
@endphp

<section class="retail-comparison bg-[var(--retail-surface)]">
    <div class="px-6">
        <div class="mx-auto max-w-3xl text-center">
            <h2
                class="mx-auto text-4xl font-black tracking-tight text-[var(--retail-ink)]"
            >
                {{ $section->heading }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mx-auto mt-4 max-w-2xl text-lg text-stone-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div
            class="mt-10 overflow-x-auto rounded-xl border border-stone-200 bg-white"
        >
            <table class="min-w-full border-collapse text-left text-sm">
                <caption class="sr-only">
                    {{ __('capell-theme-commerce::generic.comparison_caption') }}
                </caption>
                <thead>
                    <tr
                        class="border-b border-stone-200 bg-[var(--retail-panel)]"
                    >
                        <th
                            scope="col"
                            class="w-48 p-4 text-xs font-black tracking-[0.14em] text-[var(--retail-primary)] uppercase"
                        >
                            {{ __('capell-theme-commerce::generic.comparison_feature_label') }}
                        </th>
                        @foreach ($columns as $column)
                            <th
                                scope="col"
                                class="min-w-48 p-4 text-base font-black text-[var(--retail-ink)]"
                            >
                                {{ $column['title'] ?? $column['label'] ?? '' }}
                                @if (! empty($column['price']))
                                    <span
                                        class="mt-1 block text-sm font-black text-[var(--retail-accent)]"
                                    >
                                        {{ $column['price'] }}
                                    </span>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($criteria as $criterion)
                        <tr class="border-b border-stone-200 last:border-b-0">
                            <th
                                scope="row"
                                class="p-4 text-xs font-black tracking-[0.14em] text-[var(--retail-primary)] uppercase"
                            >
                                {{ $criterion['label'] ?? '' }}
                            </th>
                            @foreach ($columns as $column)
                                <td
                                    class="p-4 text-sm leading-6 text-stone-600"
                                >
                                    {{ $comparisonValue($column, $criterion) }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

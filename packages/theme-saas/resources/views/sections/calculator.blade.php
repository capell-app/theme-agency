@php
    $items = $section->items ?? $section->features ?? [];
    $defaults = $section->defaults ?? [];
@endphp

<section class="saas-calculator bg-white">
    <div class="grid gap-8 px-6 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-slate-950">
                {{ $section->heading }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mt-4 max-w-xl text-lg">{{ $section->summary }}</p>
            @endif
        </div>

        <div
            class="saas-frame bg-slate-950 p-6 text-white"
            data-saas-calculator
        >
            <div class="grid gap-5">
                <div class="grid gap-4 sm:grid-cols-3">
                    <label class="grid gap-2 text-sm font-black text-cyan-100">
                        {{ __('capell-theme-saas::generic.calculator_visitors_label') }}
                        <input
                            type="number"
                            min="0"
                            step="100"
                            value="{{ $defaults['visitors'] ?? 12000 }}"
                            data-saas-calculator-visitors
                            class="min-h-12 rounded-lg border border-white/10 bg-white px-3 text-sm font-black text-slate-950"
                        />
                    </label>

                    <label class="grid gap-2 text-sm font-black text-cyan-100">
                        {{ __('capell-theme-saas::generic.calculator_conversion_label') }}
                        <input
                            type="number"
                            min="0"
                            max="100"
                            step="0.1"
                            value="{{ $defaults['conversion'] ?? 3.2 }}"
                            data-saas-calculator-conversion
                            class="min-h-12 rounded-lg border border-white/10 bg-white px-3 text-sm font-black text-slate-950"
                        />
                    </label>

                    <label class="grid gap-2 text-sm font-black text-cyan-100">
                        {{ __('capell-theme-saas::generic.calculator_lift_label') }}
                        <input
                            type="number"
                            min="0"
                            max="100"
                            step="1"
                            value="{{ $defaults['lift'] ?? 18 }}"
                            data-saas-calculator-lift
                            class="min-h-12 rounded-lg border border-white/10 bg-white px-3 text-sm font-black text-slate-950"
                        />
                    </label>
                </div>

                <div
                    class="rounded-xl border border-white/10 bg-white/[0.05] p-5"
                >
                    <p
                        class="text-xs font-black tracking-[0.18em] text-cyan-200 uppercase"
                    >
                        {{ __('capell-theme-saas::generic.calculator_result_label') }}
                    </p>
                    <p class="mt-3 text-5xl font-black text-white">
                        <output data-saas-calculator-result>69</output>
                    </p>
                    <p class="mt-2 text-sm leading-6 text-slate-300">
                        {{ __('capell-theme-saas::generic.calculator_result_summary') }}
                    </p>
                </div>

                @foreach ($items as $item)
                    <div
                        class="rounded-xl border border-white/10 bg-white/[0.05] p-4"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="font-black text-white">
                                {{ $item['title'] ?? $item['label'] ?? '' }}
                            </h3>
                            @if ($item['metric'] ?? null)
                                <span
                                    class="rounded-full bg-cyan-300 px-3 py-1 text-xs font-black text-slate-950"
                                >
                                    {{ $item['metric'] }}
                                </span>
                            @endif
                        </div>
                        @if ($item['description'] ?? $item['summary'] ?? null)
                            <p class="mt-2 text-sm text-slate-300">
                                {{ $item['description'] ?? $item['summary'] }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        document
            .querySelectorAll('[data-saas-calculator]')
            .forEach((calculator) => {
                const visitors = calculator.querySelector(
                    '[data-saas-calculator-visitors]',
                )
                const conversion = calculator.querySelector(
                    '[data-saas-calculator-conversion]',
                )
                const lift = calculator.querySelector(
                    '[data-saas-calculator-lift]',
                )
                const result = calculator.querySelector(
                    '[data-saas-calculator-result]',
                )

                const update = () => {
                    const baseline =
                        Number(visitors.value || 0) *
                        (Number(conversion.value || 0) / 100)
                    const additional = Math.round(
                        baseline * (Number(lift.value || 0) / 100),
                    )

                    result.value = additional.toLocaleString()
                    result.textContent = result.value
                }

                ;[visitors, conversion, lift].forEach((input) =>
                    input.addEventListener('input', update),
                )
                update()
            })
    </script>
</section>

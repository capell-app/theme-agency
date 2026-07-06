@php
    /**
     * quote-path-stepper — a numbered "how to get a quote" flow. The final
     * step's CTA is the provider-resolved
     * `Capell\ThemeStudio\CallOut\View\Components\QuoteRequestPath` component
     * (form-builder / bookings / mailto fallback — see that class's
     * docblock), never a static link, and never decided from this `@php`
     * block itself.
     *
     * Two variants (theme-bar criterion 3), branched on payload `variant`:
     * "default" (vertical numbered list) and "horizontal" (a row of step
     * cards, used when this widget repeats on a shorter surface like
     * `contact`).
     */
    $heading = $widget->getMeta('heading', __('capell-theme-call-out::sections.quote_path.heading'));
    $summary = $widget->getMeta('summary', __('capell-theme-call-out::sections.quote_path.summary'));
    $variant = $widget->getMeta('variant', 'default');
    $steps = collect($widget->getMeta('steps', []))->take(10);
    $phoneNumber = $widget->getMeta('phoneNumber', '');
    $emailAddress = $widget->getMeta('emailAddress', '');
    $quoteUrl = $widget->getMeta('quoteUrl', '#');
@endphp

<section
    id="quote-path-stepper"
    class="rco-shell rco-section"
    data-widget="quote-path-stepper"
    data-variant="{{ $variant }}"
>
    <div class="rco-section-inner">
        <h2>{{ $heading }}</h2>
        <p class="rco-section-summary">{{ $summary }}</p>

        <ol class="rco-quote-stepper rco-quote-stepper--{{ $variant }}">
            @foreach ($steps as $index => $step)
                <li class="rco-quote-step">
                    <span
                        class="rco-quote-step-number"
                        aria-hidden="true"
                        >{{ $index + 1 }}</span
                    >
                    <div class="rco-quote-step-copy">
                        <h3>{{ data_get($step, 'title', '') }}</h3>
                        <p>{{ data_get($step, 'summary', '') }}</p>
                    </div>
                </li>
            @endforeach
        </ol>

        <x-capell-theme-call-out::quote-request-path
            :phone-number="$phoneNumber"
            :email-address="$emailAddress"
            :quote-url="$quoteUrl"
        />
    </div>
</section>

@php
    $heading = data_get($section, 'heading', __('capell-theme-editorial-serif::sections.subscription_panel.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-editorial-serif::sections.subscription_panel.summary'));
    $items = collect(data_get($section, 'items', data_get($section, 'plans', [])));
    $formAction = data_get($section, 'action', '/subscribe');
@endphp

<section
    id="subscription-panel"
    class="eser-section eser-section-shade"
>
    <div class="eser-section-inner">
        <p class="eser-eyebrow">
            {{ __('capell-theme-editorial-serif::sections.subscription_panel.eyebrow') }}
        </p>
        <h2>{{ $heading }}</h2>
        <hr class="eser-heading-rule" />
        @if (filled($summary))
            <p class="eser-summary">{{ $summary }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div class="eser-subscription-grid">
                @foreach ($items as $item)
                    <div class="eser-plan">
                        <h3 class="eser-plan-title">
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p class="eser-plan-body">
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif

        <form
            method="get"
            action="{{ $formAction }}"
            class="eser-form"
        >
            <div class="eser-field">
                <label
                    class="eser-label"
                    for="eser-subscribe-email"
                >
                    {{ __('capell-theme-editorial-serif::sections.subscription_panel.email_label') }}
                </label>
                <input
                    id="eser-subscribe-email"
                    class="eser-input"
                    type="email"
                    name="email"
                    placeholder="{{ __('capell-theme-editorial-serif::sections.subscription_panel.email_placeholder') }}"
                />
            </div>
            <button
                type="submit"
                class="eser-button"
            >
                {{ __('capell-theme-editorial-serif::sections.subscription_panel.button') }}
            </button>
        </form>
    </div>
</section>

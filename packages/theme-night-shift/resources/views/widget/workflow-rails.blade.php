@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-night-shift::sections.topics.heading'));
    $items = is_array($widget->getMeta('items')) ? $widget->getMeta('items') : [];
@endphp

{{--
    `dps-shell` is carried on this widget's root `<section>` for the same
    reason documented in `resources/views/widget/changelog-integrations.blade.php`.
--}}
<section
    id="workflow-rails"
    class="dps-shell dps-section dps-section-raised"
>
    <div class="dps-section-inner">
        <div class="dps-heading-row">
            <div>
                <p class="dps-eyebrow">
                    {{ __('capell-theme-night-shift::sections.topics.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
            </div>
        </div>

        <div class="dps-rail">
            @foreach ($items as $item)
                <article class="dps-rail-row">
                    <span
                        class="dps-rail-icon"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

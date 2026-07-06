{{--
    process-documentation-timeline (Wave 4a signature widget #5): uses the
    shared numbered-step timeline shape Foundation already ships for
    layout-native themes (see
    theme-foundation resources/views/components/widget/modern/process-steps.blade.php
    — numbered roundels + title/summary, horizontal or vertical layout) with a
    catalogue-plate visual skin specific to art-paper. This is a classic
    ChromeSplitBladeThemeRenderer section (not a layout-builder widget asset,
    a different data contract), so the shape is mirrored here rather than the
    Foundation Blade component included directly. Payload cap §0.3: timelines
    ≤50 steps.
--}}

<section
    id="process-documentation-timeline"
    class="dlm-section dlm-section-field"
    data-widget="process-documentation-timeline"
>
    <div class="dlm-section-inner">
        <p class="dlm-kicker">
            {{ __('capell-theme-art-paper::sections.process.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-art-paper::sections.process.heading')) }}
        </h2>
        <p class="dlm-lede">{{ data_get($section, 'summary', __('capell-theme-art-paper::sections.process.summary')) }}</p>

        <ol class="dlm-process-timeline">
            @foreach (collect(data_get($section, 'items', data_get($section, 'steps', [])))->take(50) as $step)
                <li class="dlm-process-step">
                    <span
                        class="dlm-process-plate-number"
                        aria-hidden="true"
                    >
                        {{ __('capell-theme-art-paper::sections.plate.figure') }} {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <div class="dlm-process-step-body">
                        <h3>
                            {{ data_get($step, 'title', data_get($step, 'name', '')) }}
                        </h3>
                        <p>{{ data_get($step, 'summary', data_get($step, 'description', '')) }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>

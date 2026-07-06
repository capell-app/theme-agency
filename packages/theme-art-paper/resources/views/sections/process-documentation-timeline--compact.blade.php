{{--
    process-documentation-timeline, `compact` variant: the same shared
    numbered-step shape rendered as a condensed horizontal row rather than a
    vertical spine — for placements with less vertical room (e.g. nested
    inside a detail-page sidebar rail). Payload cap §0.3: timelines ≤50 steps.
--}}

<section
    id="process-documentation-timeline"
    class="dlm-section dlm-section-field"
    data-widget="process-documentation-timeline"
    data-variant="compact"
>
    <div class="dlm-section-inner">
        <p class="dlm-kicker">
            {{ __('capell-theme-art-paper::sections.process.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-art-paper::sections.process.heading')) }}
        </h2>
        <p class="dlm-lede">{{ data_get($section, 'summary', __('capell-theme-art-paper::sections.process.summary')) }}</p>

        <ol class="dlm-process-timeline dlm-process-timeline-compact">
            @foreach (collect(data_get($section, 'items', data_get($section, 'steps', [])))->take(50) as $step)
                <li class="dlm-process-step dlm-process-step-compact">
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

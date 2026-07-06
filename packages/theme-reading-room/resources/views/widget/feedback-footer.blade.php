@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-reading-room::sections.feedback.heading'));
    $reasons = is_array($widget->getMeta('reasons')) ? $widget->getMeta('reasons') : [];
    $variant = (string) ($widget->getMeta('variant') ?? 'simple');
@endphp

{{--
    `feedback-footer` — "was this page helpful" style footer widget. Purely
    static markup: no polling, no live submission endpoint wired here (a
    real feedback package would supply its own `action` via meta in a
    future integration; this scaffold renders the on-brand shell either
    way, never reaching into any admin/editing surface from this public
    view).

    Two variants:
    - `simple` (default): a single yes/no choice row.
    - `survey`: adds a follow-up row of reason chips beneath the base
      choice, all set as fixed copy (not live-toggled).
--}}
<section
    id="feedback-footer"
    class="rr-shell rr-section"
>
    <div class="rr-section-inner">
        <div class="rr-feedback">
            <h3>{{ $heading }}</h3>

            <div class="rr-feedback-choices">
                <button
                    type="button"
                    class="rr-feedback-choice"
                >
                    {{ __('capell-theme-reading-room::sections.feedback.yes') }}
                </button>
                <button
                    type="button"
                    class="rr-feedback-choice"
                >
                    {{ __('capell-theme-reading-room::sections.feedback.no') }}
                </button>
            </div>
        </div>

        @if ($variant === 'survey' && $reasons !== [])
            <div
                class="rr-feedback-reasons"
                style="margin-top: 0.85rem"
            >
                @foreach ($reasons as $reason)
                    <span
                        class="rr-feedback-reason"
                        >{{ data_get($reason, 'label', $reason) }}</span
                    >
                @endforeach
            </div>
        @endif
    </div>
</section>

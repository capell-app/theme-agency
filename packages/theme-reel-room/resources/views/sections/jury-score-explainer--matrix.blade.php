@php
    /**
     * jury-score-matrix variant: rather than one project's scoring axes (the
     * default view), this renders several projects side by side against the
     * same scoring axes as a genuine matrix — rows are projects, columns are
     * axes, each cell the jury's percentage for that axis. Payload cap
     * §0.3: table rows capped at 100; a matrix of jury verdicts is
     * comfortably inside that.
     */
    $heading = data_get($section, 'heading', __('capell-theme-reel-room::sections.jury_score_explainer.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-reel-room::sections.jury_score_explainer.summary'));
    $axes = collect(data_get($section, 'axes', []))->take(6);
    $rows = collect(data_get($section, 'rows', []))->take(100);
@endphp

<section
    id="jury-score-explainer"
    class="mva-section mva-section-raised"
    data-widget="jury-score-matrix"
    data-variant="matrix"
>
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-reel-room::sections.jury_score_explainer.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="mva-lede">{{ $summary }}</p>

        @if ($axes->isNotEmpty() && $rows->isNotEmpty())
            <div
                class="mva-matrix-scroll"
                style="margin-top: 2rem"
            >
                <table class="mva-matrix">
                    <caption class="mva-visually-hidden">
                        {{ $heading }}
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col">
                                {{ __('capell-theme-reel-room::sections.jury_score_explainer.matrix_project_column') }}
                            </th>
                            @foreach ($axes as $axis)
                                <th scope="col">
                                    {{ data_get($axis, 'title', data_get($axis, 'name', '')) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            @php
                                $scores = collect(data_get($row, 'scores', []));
                            @endphp
                            <tr>
                                <th scope="row">
                                    {{ data_get($row, 'title', data_get($row, 'name', '')) }}
                                </th>
                                @foreach ($axes as $axisIndex => $axis)
                                    @php
                                        $rawScore = (int) data_get($scores, $axisIndex, 0);
                                        $score = max(0, min(100, $rawScore));
                                    @endphp
                                    <td>
                                        <span
                                            class="mva-matrix-cell"
                                            style="--mva-matrix-score: {{ $score }}%"
                                        >
                                            {{ $score }}%
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>

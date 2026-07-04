@php
    $heading = data_get($section, 'heading', __('capell-theme-scoreboard-showcase::sections.voting.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-scoreboard-showcase::sections.voting.summary'));
    $items = data_get($section, 'items', []);
@endphp

<section
    id="voting-status"
    class="sbs-section"
>
    <div class="sbs-section-inner">
        <p class="sbs-kicker">
            {{ __('capell-theme-scoreboard-showcase::sections.voting.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="sbs-lede">{{ $summary }}</p>

        <div class="sbs-vote-panel">
            <div class="sbs-vote-status-bar">
                <span class="sbs-vote-status-pill">
                    {{ __('capell-theme-scoreboard-showcase::sections.voting.status_open') }}
                </span>
            </div>

            <div class="sbs-vote-rows">
                @foreach ($items as $item)
                    @php
                        $itemMeta = (string) data_get($item, 'meta', '');
                        $tally = null;

                        if (preg_match('/\d[\d,]*/', $itemMeta, $matches) === 1) {
                            $tally = $matches[0];
                        }
                    @endphp

                    <div class="sbs-vote-row">
                        <span class="sbs-vote-position">
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <div>
                            <p class="sbs-vote-title">
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            </p>
                            <p class="sbs-vote-meta">
                                {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                            </p>
                        </div>
                        <span class="sbs-vote-tally">
                            {{ $tally ?? $itemMeta }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{--
    discipline-carousel-browse — sticky variant: the tablist pins to the top
    of the viewport while the active panel scrolls beneath it (a real
    "browse while filtering stays put" affordance), still the identical
    tabs.js-shaped role/aria contract and keyboard behaviour as the default
    variant.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-open-studio::sections.discipline_carousel.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-open-studio::sections.discipline_carousel.summary'));
    $disciplines = collect(data_get($section, 'disciplines', []))->take(20)->values();

    if ($disciplines->isEmpty()) {
        $disciplines = collect([
            ['title' => __('capell-theme-open-studio::sections.discipline_carousel.all_title'), 'items' => []],
        ]);
    }

    $instanceId = 'discipline-browse-sticky-' . data_get($section, 'instanceKey', 'default');
@endphp

<section
    id="discipline-carousel-browse"
    class="csp-section csp-section-field"
>
    <div class="csp-section-inner">
        <p class="csp-kicker">
            {{ __('capell-theme-open-studio::sections.discipline_carousel.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="csp-lede">{{ $summary }}</p>

        <div
            class="csp-discipline-browse csp-discipline-browse-sticky"
            data-discipline-carousel-browse
        >
            <div
                class="csp-discipline-tablist csp-discipline-tablist-sticky"
                role="tablist"
                aria-label="{{ $heading }}"
            >
                @foreach ($disciplines as $index => $discipline)
                    <button
                        type="button"
                        id="{{ $instanceId }}-tab-{{ $index }}"
                        class="csp-discipline-tab"
                        role="tab"
                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                        aria-controls="{{ $instanceId }}-panel-{{ $index }}"
                        tabindex="{{ $index === 0 ? '0' : '-1' }}"
                    >
                        {{ data_get($discipline, 'title', data_get($discipline, 'name', '')) }}
                    </button>
                @endforeach
            </div>

            @foreach ($disciplines as $index => $discipline)
                @php
                    $items = collect(data_get($discipline, 'items', []))->take(20)->values();
                @endphp

                <div
                    id="{{ $instanceId }}-panel-{{ $index }}"
                    class="csp-discipline-panel"
                    role="tabpanel"
                    aria-labelledby="{{ $instanceId }}-tab-{{ $index }}"
                    @if ($index > 0) hidden @endif
                >
                    @if ($items->isNotEmpty())
                        <div class="csp-discipline-panel-grid">
                            @foreach ($items as $item)
                                @php
                                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                                @endphp

                                <a
                                    class="csp-discipline-panel-card"
                                    href="{{ $itemUrl ?: '#project-feed' }}"
                                >
                                    @if (filled($itemImage))
                                        <img
                                            src="{{ $itemImage }}"
                                            alt="{{ $itemAlt }}"
                                            loading="lazy"
                                            decoding="async"
                                            class="csp-cover"
                                        />
                                    @else
                                        <div
                                            class="csp-cover csp-cover-empty"
                                            aria-hidden="true"
                                        ></div>
                                    @endif
                                    <p class="csp-project-meta">
                                        {{ data_get($item, 'meta', '') }}
                                    </p>
                                    <h3 style="font-size: 1.05rem; margin: 0">
                                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                    </h3>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="csp-lede">
                            {{ data_get($discipline, 'summary', __('capell-theme-open-studio::sections.discipline_carousel.empty')) }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

<script>
    ;(function () {
        var root = document.currentScript.previousElementSibling
        var browse =
            root && root.matches('[data-discipline-carousel-browse]')
                ? root
                : null
        if (!browse) {
            return
        }
        var tablist = browse.querySelector('[role="tablist"]')
        if (!tablist) {
            return
        }
        var tabs = Array.prototype.slice.call(
            tablist.querySelectorAll('[role="tab"]'),
        )
        var panels = tabs.map(function (tab) {
            var panelId = tab.getAttribute('aria-controls')
            return panelId ? document.getElementById(panelId) : null
        })
        if (tabs.length === 0) {
            return
        }

        function activate(index, moveFocus) {
            var target = ((index % tabs.length) + tabs.length) % tabs.length

            tabs.forEach(function (tab, tabIndex) {
                var isActive = tabIndex === target
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false')
                tab.setAttribute('tabindex', isActive ? '0' : '-1')
            })

            panels.forEach(function (panel, panelIndex) {
                if (!panel) {
                    return
                }
                if (panelIndex === target) {
                    panel.removeAttribute('hidden')
                } else {
                    panel.setAttribute('hidden', '')
                }
            })

            if (moveFocus) {
                tabs[target].focus()
            }
        }

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () {
                activate(index, false)
            })

            tab.addEventListener('keydown', function (event) {
                if (event.key === 'ArrowLeft') {
                    event.preventDefault()
                    activate(index - 1, true)
                    return
                }
                if (event.key === 'ArrowRight') {
                    event.preventDefault()
                    activate(index + 1, true)
                    return
                }
                if (event.key === 'Home') {
                    event.preventDefault()
                    activate(0, true)
                    return
                }
                if (event.key === 'End') {
                    event.preventDefault()
                    activate(tabs.length - 1, true)
                }
            })
        })
    })()
</script>

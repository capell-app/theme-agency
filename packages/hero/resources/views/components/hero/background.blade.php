@props([
    'background' => null,
    'instanceKey' => null,
])

@if ($background?->enabled)
    @php
        $backgroundHash = substr(hash('xxh128', implode('|', [
            (string) $instanceKey,
            $background->overlayStyle,
            ...array_map(
                static fn (mixed $property, mixed $value): string => (string) $property . ':' . (string) $value,
                array_keys($background->cssVariables()),
                $background->cssVariables(),
            ),
        ])), 0, 12);
        $gradientId = 'hero-overlay-a-' . $backgroundHash;
        $radialId = 'hero-overlay-b-' . $backgroundHash;
    @endphp

    <div
        aria-hidden="true"
        @class([
            'hero-background pointer-events-none absolute inset-0 overflow-hidden',
            'hero-background--' . $background->overlayStyle,
        ])
        style="@foreach ($background->cssVariables() as $property => $value) {{ $property }}: {{ $value }}; @endforeach"
    >
        <div class="hero-background__base"></div>

        <svg
            class="hero-background__overlay"
            viewBox="0 0 1440 840"
            preserveAspectRatio="none"
            focusable="false"
        >
            <defs>
                <linearGradient
                    id="{{ $gradientId }}"
                    x1="0"
                    x2="1"
                    y1="0"
                    y2="1"
                >
                    <stop offset="0%" stop-color="var(--hero-accent-color)" />
                    <stop
                        offset="100%"
                        stop-color="var(--hero-accent-color-alt)"
                    />
                </linearGradient>
                <radialGradient id="{{ $radialId }}" cx="72%" cy="18%" r="58%">
                    <stop
                        offset="0%"
                        stop-color="var(--hero-accent-color-alt)"
                    />
                    <stop
                        offset="100%"
                        stop-color="var(--hero-accent-color)"
                        stop-opacity="0"
                    />
                </radialGradient>
            </defs>

            <rect
                width="1440"
                height="840"
                fill="var(--hero-background-color)"
            />
            <path
                class="hero-background__mesh"
                d="M-60 160 C 230 40, 338 276, 620 134 S 1084 20, 1500 162 L 1500 -40 L -60 -40 Z"
                fill="url(#{{ $gradientId }})"
            />
            <path
                class="hero-background__mesh"
                d="M-80 690 C 260 520, 448 770, 738 620 S 1118 454, 1520 570 L 1520 900 L -80 900 Z"
                fill="url(#{{ $radialId }})"
            />
            <path
                class="hero-background__ribbons"
                d="M-90 610 C 190 470, 370 478, 596 555 C 844 640, 1000 588, 1218 450 C 1328 380, 1410 360, 1530 392"
            />
            <path
                class="hero-background__ribbons"
                d="M-80 280 C 188 188, 376 216, 602 306 C 814 390, 996 382, 1216 266 C 1346 198, 1428 196, 1530 236"
            />
            <g class="hero-background__grid">
                <path
                    d="M0 120 H1440 M0 240 H1440 M0 360 H1440 M0 480 H1440 M0 600 H1440 M0 720 H1440"
                />
                <path
                    d="M120 0 V840 M240 0 V840 M360 0 V840 M480 0 V840 M600 0 V840 M720 0 V840 M840 0 V840 M960 0 V840 M1080 0 V840 M1200 0 V840 M1320 0 V840"
                />
            </g>
            <g class="hero-background__contours">
                <path
                    d="M872 166 C1008 100 1164 124 1248 232 C1334 344 1300 488 1188 572 C1054 672 844 634 770 486 C704 354 748 224 872 166Z"
                />
                <path
                    d="M922 236 C1012 194 1116 210 1170 280 C1228 354 1204 452 1130 508 C1040 576 900 548 850 448 C806 358 840 276 922 236Z"
                />
                <path
                    d="M238 512 C346 456 472 476 536 562 C602 650 574 760 482 816 C370 884 208 834 162 718 C124 624 156 554 238 512Z"
                />
            </g>
        </svg>
    </div>
@endif

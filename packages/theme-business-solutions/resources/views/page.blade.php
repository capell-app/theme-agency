@once
    <style>
        .business-theme-shell {
            background: var(--theme-surface, #f8fafc);
            color: var(--theme-foreground, #111827);
            font-family: var(--theme-body-font, Inter, system-ui, sans-serif);
        }

        .business-theme-shell *,
        .business-theme-shell ::before,
        .business-theme-shell ::after {
            box-sizing: border-box;
        }

        .business-theme-shell a {
            color: inherit;
        }

        .business-theme-shell h1,
        .business-theme-shell h2,
        .business-theme-shell h3 {
            color: var(--theme-foreground, #111827);
            font-family: var(--theme-heading-font, inherit);
            letter-spacing: 0;
        }

        .business-theme-shell section {
            padding: clamp(3.5rem, 7vw, 6rem) 1.25rem;
        }

        .business-theme-container {
            margin-inline: auto;
            max-width: 76rem;
        }

        .business-theme-card {
            background: color-mix(
                in srgb,
                var(--theme-surface, #ffffff) 22%,
                #ffffff
            );
            border: 1px solid
                color-mix(
                    in srgb,
                    var(--theme-neutral, #111827) 16%,
                    transparent
                );
            border-radius: var(--theme-radius-value, 0.5rem);
        }

        .business-theme-button {
            align-items: center;
            background: var(--theme-primary, #111827);
            border-radius: var(--theme-radius-value, 0.5rem);
            color: #ffffff;
            display: inline-flex;
            font-weight: 750;
            gap: 0.5rem;
            justify-content: center;
            padding: 0.875rem 1.125rem;
            text-decoration: none;
        }

        .business-theme-button-secondary {
            background: transparent;
            border: 1px solid
                color-mix(
                    in srgb,
                    var(--theme-neutral, #111827) 24%,
                    transparent
                );
            color: var(--theme-foreground, #111827);
        }

        .business-theme-skip-link {
            background: var(--theme-foreground, #111827);
            color: var(--theme-surface, #ffffff);
            font-weight: 800;
            left: 1rem;
            padding: 0.75rem 1rem;
            position: fixed;
            top: 1rem;
            transform: translateY(-150%);
            z-index: 50;
        }

        .business-theme-skip-link:focus {
            transform: translateY(0);
        }

        .business-theme-shell[data-business-layout='search-hero']
            .business-theme-hero,
        .business-theme-shell[data-business-layout='booking-hero']
            .business-theme-hero {
            min-height: min(82vh, 48rem);
        }

        .business-theme-shell[data-business-modern='1'] .business-theme-hero {
            background:
                linear-gradient(
                    135deg,
                    color-mix(
                        in srgb,
                        var(--theme-primary, #111827) 10%,
                        transparent
                    ),
                    transparent 38%
                ),
                radial-gradient(
                    circle at 92% 12%,
                    color-mix(
                        in srgb,
                        var(--theme-accent, #111827) 18%,
                        transparent
                    ),
                    transparent 22rem
                );
        }

        .business-theme-shell[data-business-layout='form-hero']
            .business-theme-hero,
        .business-theme-shell[data-business-layout='ticker-hero']
            .business-theme-hero,
        .business-theme-shell[data-business-layout='spec-hero']
            .business-theme-hero,
        .business-theme-shell[data-business-layout='service-directory']
            .business-theme-hero {
            background:
                linear-gradient(
                    180deg,
                    color-mix(
                        in srgb,
                        var(--theme-primary, #111827) 8%,
                        transparent
                    ),
                    transparent
                ),
                var(--theme-surface, #f8fafc);
        }

        .business-theme-shell[data-business-layout='split-modern']
            .business-theme-card,
        .business-theme-shell[data-business-modern='1'] .business-theme-card {
            box-shadow: 0 18px 55px
                color-mix(
                    in srgb,
                    var(--theme-primary, #111827) 12%,
                    transparent
                );
        }

        .business-theme-visual-panel {
            background: linear-gradient(
                145deg,
                color-mix(in srgb, var(--theme-primary, #111827) 14%, #ffffff),
                color-mix(in srgb, var(--theme-accent, #111827) 10%, #ffffff)
            );
            border: 1px solid
                color-mix(
                    in srgb,
                    var(--theme-primary, #111827) 18%,
                    transparent
                );
            border-radius: calc(var(--theme-radius-value, 0.5rem) + 0.75rem);
            overflow: hidden;
            padding: 1rem;
            position: relative;
        }

        .business-theme-visual-panel::before {
            background:
                linear-gradient(
                    color-mix(
                            in srgb,
                            var(--theme-primary, #111827) 14%,
                            transparent
                        )
                        1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    color-mix(
                            in srgb,
                            var(--theme-primary, #111827) 14%,
                            transparent
                        )
                        1px,
                    transparent 1px
                );
            background-size: 2rem 2rem;
            content: '';
            inset: 0;
            opacity: 0.42;
            pointer-events: none;
            position: absolute;
        }

        .business-theme-visual-panel > * {
            position: relative;
        }

        .business-theme-mini-card {
            background: rgb(255 255 255 / 82%);
            border: 1px solid rgb(255 255 255 / 68%);
            border-radius: var(--theme-radius-value, 0.5rem);
            padding: 1rem;
        }

        .business-theme-shell[data-business-layout='spec-hero']
            .business-theme-mini-card,
        .business-theme-shell[data-business-layout='service-directory']
            .business-theme-mini-card,
        .business-theme-shell[data-business-layout='ticker-hero']
            .business-theme-mini-card {
            background: #ffffff;
            border-color: color-mix(
                in srgb,
                var(--theme-neutral, #111827) 18%,
                transparent
            );
        }

        .business-theme-shell[data-business-layout='catalog-grid']
            .business-theme-section-grid {
            grid-template-columns: minmax(12rem, 0.32fr) minmax(0, 1fr);
        }

        @media (max-width: 760px) {
            .business-theme-shell section {
                padding: 3rem 1rem;
            }

            .business-theme-shell[data-business-layout='catalog-grid']
                .business-theme-section-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endonce

<a href="#main-content" class="business-theme-skip-link">
    {{ __('capell-theme-business-solutions::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (string $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    data-business-layout="{{ $profile['layout'] }}"
    data-business-modern="{{ $profile['modern'] ? '1' : '0' }}"
    class="business-theme-shell min-h-screen antialiased"
>
    {!! $content !!}
</div>

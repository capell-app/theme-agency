@once
    <style>
        .velocity-shell {
            --velocity-ink: var(--theme-foreground, #0f172a);
            --velocity-muted: #475569;
            --velocity-line: #dbeafe;
            --velocity-primary: var(--theme-primary, #2563eb);
            --velocity-accent: var(--theme-accent, #06b6d4);
            --velocity-surface: var(--theme-surface, #f8fafc);

            background: linear-gradient(
                180deg,
                #ffffff 0%,
                var(--velocity-surface) 48%,
                #ffffff 100%
            );
            color: var(--velocity-ink);
            font-family: var(
                --theme-body-font,
                Inter,
                ui-sans-serif,
                system-ui,
                sans-serif
            );
        }

        .velocity-shell section {
            padding: clamp(4.5rem, 8vw, 7.5rem) 1.5rem;
        }

        .velocity-shell section > div,
        .velocity-shell footer > div,
        .velocity-shell nav > div {
            max-width: 76rem;
            margin-inline: auto;
        }

        .velocity-shell h1,
        .velocity-shell h2,
        .velocity-shell h3 {
            color: var(--velocity-ink);
            font-family: var(--theme-heading-font, inherit);
            letter-spacing: 0;
        }

        .velocity-shell h1 {
            max-width: 12ch;
            font-size: clamp(3.25rem, 7vw, 6.75rem);
            font-weight: 850;
            line-height: 0.95;
        }

        .velocity-shell h2 {
            max-width: 15ch;
            font-size: clamp(2.25rem, 4vw, 4.25rem);
            font-weight: 800;
            line-height: 1;
        }

        .velocity-shell p {
            color: var(--velocity-muted);
            line-height: 1.75;
        }

        .velocity-skip-link {
            background: #0f172a;
            color: #ffffff;
            font-weight: 800;
            left: 1rem;
            padding: 0.75rem 1rem;
            position: fixed;
            top: 1rem;
            transform: translateY(-150%);
            z-index: 50;
        }

        .velocity-skip-link:focus {
            transform: translateY(0);
        }

        .velocity-frame {
            border: 1px solid
                color-mix(in srgb, var(--velocity-primary) 18%, #dbeafe);
            border-radius: 1rem;
            box-shadow: 0 24px 70px rgb(15 23 42 / 12%);
        }

        .velocity-cta {
            align-items: center;
            border-radius: 999px;
            display: inline-flex;
            font-size: 0.875rem;
            font-weight: 800;
            gap: 0.5rem;
            justify-content: center;
            min-height: 2.875rem;
            padding: 0.75rem 1.125rem;
        }

        .velocity-cta-primary {
            background: var(--velocity-primary);
            color: #ffffff;
        }

        .velocity-cta-secondary {
            border: 1px solid #cbd5e1;
            color: var(--velocity-ink);
        }
    </style>
@endonce

<a href="#main-content" class="velocity-skip-link">
    {{ __('capell-theme-saas::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="velocity-shell min-h-screen antialiased"
>
    {!! $content !!}
</div>

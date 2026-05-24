@once
    <style>
        .healthcare-shell {
            --healthcare-ink: var(--theme-foreground, #14323a);
            --healthcare-muted: #5c7280;
            --healthcare-line: #d9e8ee;
            --healthcare-primary: var(--theme-primary, #0f766e);
            --healthcare-link: #2563eb;
            --healthcare-accent: var(--theme-accent, #f59e0b);
            --healthcare-surface: var(--theme-surface, #f6fbfd);

            background: linear-gradient(
                180deg,
                #f6fbfd 0%,
                #ffffff 42%,
                var(--healthcare-surface) 100%
            );
            color: var(--healthcare-ink);
            font-family: var(
                --theme-body-font,
                Inter,
                ui-sans-serif,
                system-ui,
                sans-serif
            );
        }

        .healthcare-shell section {
            padding: clamp(4.5rem, 8vw, 7.5rem) 1.5rem;
        }

        .healthcare-shell section > div,
        .healthcare-shell footer > div,
        .healthcare-shell nav > div {
            max-width: 76rem;
            margin-inline: auto;
        }

        :where(
            .healthcare-shell h1,
            .healthcare-shell h2,
            .healthcare-shell h3
        ) {
            color: var(--healthcare-ink);
            font-family: var(--theme-heading-font, inherit);
            letter-spacing: 0;
        }

        :where(.healthcare-shell h1) {
            max-width: 12ch;
            font-size: clamp(3.25rem, 7vw, 6.75rem);
            font-weight: 850;
            line-height: 0.95;
        }

        :where(.healthcare-shell h2) {
            max-width: 15ch;
            font-size: clamp(2.25rem, 4vw, 4.25rem);
            font-weight: 800;
            line-height: 1;
        }

        :where(.healthcare-shell p) {
            color: var(--healthcare-muted);
            line-height: 1.75;
        }

        .healthcare-skip-link {
            background: #14323a;
            color: #ffffff;
            font-weight: 800;
            left: 1rem;
            padding: 0.75rem 1rem;
            position: fixed;
            top: 1rem;
            transform: translateY(-150%);
            z-index: 50;
        }

        .healthcare-skip-link:focus {
            transform: translateY(0);
        }

        .healthcare-frame {
            border: 1px solid
                color-mix(
                    in srgb,
                    var(--healthcare-primary) 18%,
                    var(--healthcare-line)
                );
            border-radius: 0.5rem;
            box-shadow: 0 24px 70px rgb(20 50 58 / 10%);
        }

        .healthcare-cta {
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

        .healthcare-cta-primary {
            background: var(--healthcare-primary);
            color: #ffffff;
        }

        .healthcare-cta-secondary {
            border: 1px solid #cbd5e1;
            color: var(--healthcare-ink);
        }
    </style>
@endonce

<a href="#main-content" class="healthcare-skip-link">
    {{ __('capell-theme-healthcare::generic.skip_to_content') }}
</a>

<div
    style="{{ collect($brand->tokens())->map(fn (mixed $value, string $token): string => $token . ':' . $value)->implode(';') }}"
    class="healthcare-shell min-h-screen antialiased"
>
    {!! $content !!}
</div>

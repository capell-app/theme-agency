Theme Liquid Glass renders the free glass interface for Capell Theme Studio
output. - Composer: `capell-app/theme-liquid-glass` - Read
`vendor/capell-app/theme-liquid-glass/README.md`. - Keep renderer changes behind
`LiquidGlassThemeServiceProvider`. - Public Blade must consume hydrated section
data only and must not query models or expose authoring metadata. - User-facing
fallback copy belongs in `capell-theme-liquid-glass::generic`.

# Theme Agency reference

<!-- prettier-ignore-start -->

Developer reference for `capell-app/theme-agency`. Start with the [README](../README.md) for what the extension does and how to install it.

## Package Details

Theme Agency is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-agency` and extends these surfaces: frontend.

- Status: Available
- Tier: free
- Bundle: themes
- Composer package: `capell-app/theme-agency`
- Namespace: `Capell\ThemeAgency`
- Theme key: `agency`

## Technical Shape

### Service providers

- `Capell\ThemeAgency\AgencyThemeServiceProvider`
- `Capell\ThemeAgency\Providers\ConsoleServiceProvider`

### Actions

- `InstallAgencyThemeDemoAction`

### Command signatures

- `capell:theme-agency-demo`

### Console command classes

- `DemoCommand`

### Manifest contributions

- `admin-page: Capell\ThemeAgency\Manifest\ThemeManagementPageContribution`

### Health checks

- `Capell\ThemeAgency\Health\ThemeAgencyHealthCheck`

### Blade views

- `packages/theme-agency/resources/views/page.blade.php`
- `packages/theme-agency/resources/views/sections/awarded-profiles--spotlight.blade.php`
- `packages/theme-agency/resources/views/sections/awarded-profiles.blade.php`
- `packages/theme-agency/resources/views/sections/content-listing.blade.php`
- `packages/theme-agency/resources/views/sections/creator-directory.blade.php`
- `packages/theme-agency/resources/views/sections/cta.blade.php`
- `packages/theme-agency/resources/views/sections/education-upsell--cta.blade.php`
- `packages/theme-agency/resources/views/sections/education-upsell.blade.php`
- `packages/theme-agency/resources/views/sections/featured-portfolios--parallax.blade.php`
- `packages/theme-agency/resources/views/sections/featured-portfolios.blade.php`
- `packages/theme-agency/resources/views/sections/filter-taxonomies--grid.blade.php`
- `packages/theme-agency/resources/views/sections/filter-taxonomies.blade.php`
- `packages/theme-agency/resources/views/sections/footer.blade.php`
- `packages/theme-agency/resources/views/sections/form.blade.php`
- `packages/theme-agency/resources/views/sections/hero.blade.php`
- `packages/theme-agency/resources/views/sections/navigation.blade.php`
- `packages/theme-agency/resources/views/sections/newsletter.blade.php`
- `packages/theme-agency/resources/views/sections/portfolio-grid--gallery-wall.blade.php`
- `packages/theme-agency/resources/views/sections/portfolio-grid.blade.php`
- `packages/theme-agency/resources/views/sections/proof.blade.php`
- `packages/theme-agency/resources/views/widget/section.blade.php`

### Cache tags

- `theme-agency`


## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

<!-- prettier-ignore-end -->

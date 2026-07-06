<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CallOut\Enums;

use Capell\ThemeStudio\CallOut\CallOutThemeServiceProvider;

/**
 * Call Out's own bespoke layout-builder widget component keys.
 *
 * Registered against the shared, cross-package
 * `Capell\Core\Support\Renderables\RenderableRegistry` (type
 * `layout-widget`) by {@see CallOutThemeServiceProvider}, mirroring the
 * pattern established by `Capell\ThemeStudio\NightShift\Enums\WidgetComponentEnum`
 * for the reference layout-native theme conversion.
 *
 * These eight are Call Out's Part 2 §E signature widgets for the
 * service-business vertical (quote-led trades and local services):
 * locality coverage, before/after proof, the state-driven emergency
 * availability banner (this theme's headline "urgency" mechanic), a
 * numbered quote path, accreditation strips, a review wall, a pricing
 * guide table, and team-on-the-road cards. `navigation` / `footer` are
 * wired separately through the shared Foundation header/footer chrome
 * (Call Out registers no bespoke header/footer interceptor), not through
 * this enum.
 */
enum WidgetComponentEnum: string
{
    case ServiceAreaMapGrid = 'capell.widget.call-out.service-area-map-grid';
    case BeforeAfterComparison = 'capell.widget.call-out.before-after-comparison';
    case EmergencyAvailabilityBanner = 'capell.widget.call-out.emergency-availability-banner';
    case QuotePathStepper = 'capell.widget.call-out.quote-path-stepper';
    case AccreditationInsuranceStrips = 'capell.widget.call-out.accreditation-insurance-strips';
    case ReviewProofWall = 'capell.widget.call-out.review-proof-wall';
    case PricingGuideTable = 'capell.widget.call-out.pricing-guide-table';
    case TeamOnTheRoadCards = 'capell.widget.call-out.team-on-the-road-cards';
}

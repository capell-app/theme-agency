<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\BeautySpa\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Beauty & Spa theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (treatment-menu /
 * therapist-profiles / package-grid / before-after-proof / booking-panel)
 * alongside the standard hero/proof/features/cta — giving every surface a full,
 * individual spa site rather than the shared five-section skeleton.
 */
final class BeautySpaDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'The Old Town Spa';

    /**
     * @return array<int, ThemeDemoPageDefinition>
     */
    public function definitions(string $themeKey, string $themeName, string $baseUrl): array
    {
        $media = ThemeDemoMedia::groupedForTheme($themeKey);

        return [
            $this->homepage($themeKey, $media),
            $this->directory($themeKey, $media),
            $this->detail($themeKey, $media),
            $this->contact($themeKey, $media),
            $this->empty($themeKey, $media),
            $this->notFound($themeKey, $media),
            $this->cta($themeKey, $media),
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function homepage(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'homepage',
            name: self::BRAND . ' Home',
            title: self::BRAND . ' — Boutique Wellness & Treatment Spa',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'An hour that resets everything',
                'A boutique day spa for facials, massage, and seasonal rituals, with skilled therapists and easy booking.',
            ),
            renderData: [
                'summary' => 'A boutique day spa in the old town. Restorative facials, deep-tissue massage, and seasonal rituals delivered by therapists who remember your name.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Beauty & spa',
                        'heading' => 'An hour that resets everything',
                        'summary' => 'Step off the high street and into a calmer room. Tailored facials, restorative massage, and seasonal rituals, booked in under a minute.',
                        'actions' => [
                            ['label' => 'Book a treatment', 'url' => '#booking', 'style' => 'primary'],
                            ['label' => 'View treatments', 'url' => '#treatments', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'A treatment room at The Old Town Spa',
                    ],
                    $this->treatmentMenuSection(
                        heading: 'Treatments to suit the day you have had',
                        summary: 'Every treatment starts with a quiet consultation so your therapist can tailor pressure, products, and pace to you.',
                    ),
                    $this->therapistProfilesSection(
                        heading: 'Therapists who remember your name',
                        summary: 'A small, fully qualified team. You can request the same therapist every visit so your treatment builds on the last.',
                    ),
                    $this->packageGridSection(
                        heading: 'Half-day packages for a proper reset',
                        summary: 'Bundled rituals that pair treatments, downtime, and a herbal tea by the courtyard. Ideal for birthdays and slow Sundays.',
                    ),
                    $this->beforeAfterProofSection(
                        heading: 'Real skin, real results',
                        summary: 'Six-week before-and-afters from regular guests, shared with their permission. No filters, no retouching.',
                    ),
                    $this->bookingPanelSection(
                        heading: 'Booking that feels like part of the calm',
                        summary: 'Pick a treatment, choose a therapist, and lock in a time. We hold a fifteen-minute buffer around every appointment so you never feel rushed.',
                    ),
                    $this->featuresSection(
                        heading: 'The little things we never skip',
                        summary: 'Details that turn an appointment into an afternoon you look forward to.',
                    ),
                    $this->proofSection(
                        heading: 'Guests keep coming back',
                        summary: 'A decade of treatments in the old town, and the numbers that come with it.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to switch off for an hour?',
                        summary: 'Treatments book up fastest on weekends. Reserve your slot now and arrive ten minutes early for a herbal tea.',
                    ),
                ],
            ],
            type: PageTypeEnum::Home,
            layout: LayoutEnum::Home,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function directory(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'directory',
            name: self::BRAND . ' Treatments',
            title: 'Treatment menu — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The full treatment menu',
                'Browse every facial, massage, and ritual, grouped by what you need most that week.',
            ),
            renderData: [
                'summary' => 'The full treatment menu, from a fifteen-minute express facial to a three-hour signature ritual. Filter by need, time, or therapist.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Treatment menu',
                        'heading' => 'Browse every treatment we offer',
                        'summary' => 'Facials, massage, body rituals, and seasonal specials, all in one place. Sort by what you have time for or how you want to feel.',
                        'actions' => [
                            ['label' => 'Book a treatment', 'url' => '#booking', 'style' => 'primary'],
                            ['label' => 'Talk to a therapist', 'url' => '#contact', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Treatment products on a spa shelf',
                    ],
                    $this->treatmentMenuSection(
                        heading: 'Facials & skin treatments',
                        summary: 'Targeted skin work, from a quick glow-up before an event to a full corrective course.',
                    ),
                    $this->contentListingSection(
                        heading: 'More rituals & body treatments',
                        summary: 'Massage, body wraps, and seasonal specials for when your whole body needs the attention.',
                    ),
                    $this->ctaSection(
                        heading: 'Not sure which treatment is right?',
                        summary: 'Tell us what is going on with your skin or where you hold tension, and a therapist will recommend the best fit.',
                    ),
                ],
            ],
            layout: LayoutEnum::Results,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function detail(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'detail',
            name: self::BRAND . ' Signature Facial',
            title: 'The Old Town Signature Facial — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'The Old Town Signature Facial',
                'Seventy-five minutes of double cleansing, gentle resurfacing, lymphatic massage, and a bespoke mask.',
            ),
            renderData: [
                'summary' => 'Our most-booked treatment. Seventy-five minutes of double cleansing, gentle resurfacing, lymphatic facial massage, and a mask mixed for your skin on the day.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Signature treatment',
                        'heading' => 'The Old Town Signature Facial',
                        'summary' => 'Seventy-five minutes, £95. A full skin reset with double cleansing, gentle resurfacing, lymphatic massage, and a mask blended for your skin that day.',
                        'actions' => [
                            ['label' => 'Book this facial', 'url' => '#booking', 'style' => 'primary'],
                            ['label' => 'Back to all treatments', 'url' => '#treatments', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'A guest receiving the signature facial',
                    ],
                    $this->treatmentMenuSection(
                        heading: 'What happens in your seventy-five minutes',
                        summary: 'Each stage flows into the next so you never feel processed. Here is the ritual, start to finish.',
                    ),
                    $this->therapistProfilesSection(
                        heading: 'Therapists who perform this facial',
                        summary: 'Request any of them when you book. Each is trained on our full product range and tailors every step to your skin.',
                    ),
                    $this->beforeAfterProofSection(
                        heading: 'Before & after a course of three',
                        summary: 'What regular guests see after three signature facials spaced four weeks apart.',
                    ),
                    $this->bookingPanelSection(
                        heading: 'Reserve the signature facial',
                        summary: 'Choose a date, pick your therapist, and add a scalp massage if you fancy it. We confirm by text within the hour.',
                    ),
                    $this->ctaSection(
                        heading: 'Make it a half-day',
                        summary: 'Pair this facial with a back massage and courtyard tea in our Reset package, and save against booking separately.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function contact(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'contact',
            name: self::BRAND . ' Booking',
            title: 'Book & find us — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Book a treatment or find the spa',
                'Reserve online, call the front desk, or drop in. We are two minutes from the old market square.',
            ),
            renderData: [
                'summary' => 'Reserve online, call the front desk, or pop in. We are tucked just off the old market square, open seven days, with late slots on Thursdays.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Booking & contact',
                        'heading' => 'Book a treatment or find us',
                        'summary' => 'Two minutes from the old market square, open seven days a week with late appointments on Thursdays. Call 01234 567 890 or book online below.',
                        'actions' => [
                            ['label' => 'Book online', 'url' => '#booking', 'style' => 'primary'],
                            ['label' => 'Call the front desk', 'url' => 'tel:+441234567890', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The reception at The Old Town Spa',
                    ],
                    $this->bookingPanelSection(
                        heading: 'Everything you need to plan your visit',
                        summary: 'Hours, parking, what to wear, and how early to arrive, all in one place so your first visit feels effortless.',
                    ),
                    $this->proofSection(
                        heading: 'What to expect when you arrive',
                        summary: 'A little of what guests say about their first visit to the spa.',
                    ),
                    $this->ctaSection(
                        heading: 'Got a question before you book?',
                        summary: 'Message the front desk and a therapist will reply the same day with honest advice, no pressure to book.',
                    ),
                ],
            ],
            layout: LayoutEnum::System,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function empty(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'empty',
            name: self::BRAND . ' No Matches',
            title: 'No treatments match — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No treatments match that filter yet',
                'A calm empty state that guides guests back to the full treatment menu.',
            ),
            renderData: [
                'summary' => 'No treatments match that filter just now, but the front desk can still find you a slot that fits.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Treatment menu',
                        'heading' => 'No treatments match that filter yet',
                        'summary' => 'Nothing fits that exact combination of time and need right now. Clear the filter to see the full menu, or tell us what you are after.',
                        'actions' => [
                            ['label' => 'View all treatments', 'url' => '#treatments', 'style' => 'primary'],
                            ['label' => 'Ask a therapist', 'url' => '#contact', 'style' => 'secondary'],
                        ],
                    ],
                    $this->treatmentMenuSection(
                        heading: 'Most-loved treatments instead',
                        summary: 'While you are here, these are the treatments guests book again and again.',
                    ),
                    $this->packageGridSection(
                        heading: 'Or make an afternoon of it',
                        summary: 'If a single treatment is not quite enough, our packages pair a few favourites into one calm visit.',
                    ),
                    $this->ctaSection(
                        heading: 'Tell us what you were looking for',
                        summary: 'Describe what you need and the front desk will match you to the right treatment and therapist.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function notFound(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'not-found',
            name: self::BRAND . ' 404',
            title: 'Page not found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'This page has drifted off',
                'A reassuring not-found page that points guests back to booking and the treatment menu.',
            ),
            renderData: [
                'summary' => 'That page has drifted off somewhere quiet, but the way back to your treatment is easy.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page has drifted off somewhere quiet',
                        'summary' => 'The link is broken or the page has moved on. Take a breath, then head back to the treatment menu or book your next visit.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View treatments', 'url' => '#treatments', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Or jump straight to booking',
                        summary: 'You came here to relax, not to hunt for links. Reserve a treatment in under a minute.',
                    ),
                ],
            ],
            type: PageTypeEnum::NotFound,
            layout: LayoutEnum::System,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function cta(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'cta',
            name: self::BRAND . ' Reserve',
            title: 'Book your treatment — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Treat yourself to an hour that resets everything',
                'A focused booking page that turns a relaxed browse into a reserved treatment.',
            ),
            renderData: [
                'summary' => 'Treat yourself to an hour that resets everything. Reserve a treatment with the therapist of your choice.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Reserve your visit',
                        'heading' => 'Treat yourself to an hour that resets everything',
                        'summary' => 'Whether it is a quick express facial or a full half-day ritual, the same calm room and the same skilled hands are waiting.',
                        'actions' => [
                            ['label' => 'Book a treatment', 'url' => '#booking', 'style' => 'primary'],
                            ['label' => 'Buy a gift voucher', 'url' => '#vouchers', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'The courtyard at The Old Town Spa',
                    ],
                    $this->proofSection(
                        heading: 'Why guests choose the old town',
                        summary: 'A decade of treatments, and what keeps people booking again.',
                    ),
                    $this->ctaSection(
                        heading: 'One treatment away from a better week',
                        summary: 'Reserve now and arrive ten minutes early for a herbal tea by the courtyard before your therapist collects you.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function treatmentMenuSection(string $heading, string $summary): array
    {
        return [
            'type' => 'treatment-menu',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Express Glow Facial — 30 min',
                    'summary' => 'A cleanse, gentle exfoliation, and hydrating mask for tired skin before an event or a big day.',
                ],
                [
                    'title' => 'The Old Town Signature Facial — 75 min',
                    'summary' => 'Our most-booked treatment. Double cleansing, resurfacing, lymphatic massage, and a bespoke mask.',
                ],
                [
                    'title' => 'Deep-Tissue Back & Shoulder — 45 min',
                    'summary' => 'Focused pressure for desk-bound shoulders and the knot that never quite leaves.',
                ],
                [
                    'title' => 'Warm Stone Full-Body Massage — 90 min',
                    'summary' => 'Heated basalt stones and slow strokes to loosen the whole body and quiet a busy mind.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function therapistProfilesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'therapist-profiles',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Marta Lewandowska — Lead Aesthetician',
                    'summary' => 'Fifteen years in skin. Marta leads our facial training and loves a stubborn skin barrier she can repair.',
                ],
                [
                    'title' => 'Daniel Owusu — Senior Massage Therapist',
                    'summary' => 'Sports and deep-tissue trained. Daniel finds the tension you forgot you were carrying.',
                ],
                [
                    'title' => 'Sofia Reyes — Holistic Therapist',
                    'summary' => 'Aromatherapy and reflexology specialist. Sofia builds rituals that calm the nervous system, not just the skin.',
                ],
                [
                    'title' => 'Priya Shah — Skin & Body Therapist',
                    'summary' => 'Our resurfacing and body-wrap expert, known for results-led courses and honest aftercare advice.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function packageGridSection(string $heading, string $summary): array
    {
        return [
            'type' => 'package-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'The Reset — half day',
                    'summary' => 'Signature facial, back massage, and a courtyard tea with downtime in between. Our most popular package.',
                ],
                [
                    'title' => 'Two Hands, One Hour — for couples',
                    'summary' => 'Side-by-side massages in our double room, finished with chilled fizz. Booked together, relaxed together.',
                ],
                [
                    'title' => 'The Bride-to-Be',
                    'summary' => 'A glow-building facial course in the run-up, plus a morning-of express treatment so you arrive radiant.',
                ],
                [
                    'title' => 'Monthly Member',
                    'summary' => 'One signature treatment every month at a held rate, plus priority weekend slots and product perks.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function beforeAfterProofSection(string $heading, string $summary): array
    {
        return [
            'type' => 'before-after-proof',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Clearer skin in six weeks',
                    'summary' => 'Hannah came in with congestion along the jaw. Three signature facials and a simple home routine later, it had cleared.',
                ],
                [
                    'title' => 'Calmer, less reactive skin',
                    'summary' => 'After a gentle barrier-repair course, James went from flaring at every product to a steady, comfortable complexion.',
                ],
                [
                    'title' => 'Brighter under the eyes',
                    'summary' => 'A course of lymphatic facials softened the puffiness Aisha had blamed on late nights for years.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingPanelSection(string $heading, string $summary): array
    {
        return [
            'type' => 'booking-panel',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => '1. Choose your treatment',
                    'summary' => 'Pick from the menu, or tell us how you want to feel and we will suggest the right fit.',
                ],
                [
                    'title' => '2. Pick your therapist & time',
                    'summary' => 'Request a favourite therapist or take the next available slot. Late times on Thursdays.',
                ],
                [
                    'title' => '3. Confirm by text',
                    'summary' => 'We hold a fifteen-minute buffer around every appointment and confirm within the hour.',
                ],
                [
                    'title' => 'Open seven days',
                    'summary' => 'Mon to Sat 9am–7pm, Sun 10am–4pm. Two minutes from the old market square, parking on Mill Lane.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function featuresSection(string $heading, string $summary): array
    {
        return [
            'type' => 'features',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'A consultation every time',
                    'summary' => 'Your therapist checks in on your skin and your week before they lay a hand on you.',
                ],
                [
                    'title' => 'Clean, conscious products',
                    'summary' => 'Cruelty-free ranges, refillable where we can, and nothing tested on animals.',
                ],
                [
                    'title' => 'A proper buffer',
                    'summary' => 'No back-to-back rushing. You get the room to yourself, before and after.',
                ],
                [
                    'title' => 'Aftercare you can actually follow',
                    'summary' => 'Simple, honest advice and a routine that fits real life, never a hard sell.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary): array
    {
        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'gallery',
            'items' => [
                [
                    'title' => 'Aromatherapy Full-Body Massage — 60 min',
                    'summary' => 'A blend mixed to your mood, worked in with slow, grounding strokes from head to toe.',
                ],
                [
                    'title' => 'Seaweed Detox Body Wrap — 75 min',
                    'summary' => 'A warming mineral wrap to soften skin and ease heaviness, finished with a light body lotion.',
                ],
                [
                    'title' => 'Scalp & Shoulder Ritual — 30 min',
                    'summary' => 'A focused, fully clothed treatment for tension headaches and screen-tired shoulders.',
                ],
                [
                    'title' => 'Seasonal Pumpkin Resurfacing Facial — 60 min',
                    'summary' => 'Our autumn special. A gentle enzyme peel to brighten dull, post-summer skin.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(string $heading, string $summary): array
    {
        return [
            'type' => 'proof',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['metric' => '4.9/5', 'name' => 'From 600+ guest reviews', 'quote' => 'Calm room, skilled hands, and never once a hard sell.'],
                ['metric' => '10 yrs', 'name' => 'In the old town', 'quote' => 'A decade of treatments and a lot of familiar faces.'],
                ['metric' => '70%', 'name' => 'Guests who rebook', 'quote' => 'Most people leave with their next appointment already booked.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Book a treatment', 'url' => '#booking', 'style' => 'primary'],
                ['label' => 'View treatments', 'url' => '#treatments', 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brandName' => self::BRAND,
            'items' => [
                ['label' => 'Treatments', 'url' => '#treatments'],
                ['label' => 'Packages', 'url' => '#packages'],
                ['label' => 'Therapists', 'url' => '#therapists'],
                ['label' => 'Booking', 'url' => '#booking'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'ctaLabel' => 'Book a treatment',
            'ctaUrl' => '#booking',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A boutique day spa just off the old market square. Open seven days a week.',
            'columns' => [
                [
                    'heading' => 'Treatments',
                    'links' => [
                        ['label' => 'Facials', 'url' => '#treatments'],
                        ['label' => 'Massage', 'url' => '#treatments'],
                        ['label' => 'Body rituals', 'url' => '#treatments'],
                        ['label' => 'Packages', 'url' => '#packages'],
                    ],
                ],
                [
                    'heading' => 'The spa',
                    'links' => [
                        ['label' => 'Our therapists', 'url' => '#therapists'],
                        ['label' => 'Gift vouchers', 'url' => '#vouchers'],
                        ['label' => 'Membership', 'url' => '#packages'],
                    ],
                ],
                [
                    'heading' => 'Visit',
                    'links' => [
                        ['label' => 'Book online', 'url' => '#booking'],
                        ['label' => 'Find us', 'url' => '#contact'],
                        ['label' => '01234 567 890', 'url' => 'tel:+441234567890'],
                    ],
                ],
            ],
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}

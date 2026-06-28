<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FitnessWellness\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Fitness & Wellness theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (class-schedule /
 * coach-profiles / membership-plans / challenge-board / nutrition-guides /
 * features / proof) alongside the standard hero/cta — giving every surface a
 * full studio site rather than the shared five-section skeleton. Copy and
 * brand tokens are mined verbatim from FitnessWellnessScreenshotRenderer.
 */
final class FitnessWellnessDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Forge Strength & Wellness';

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
            title: self::BRAND . ' — Boutique Gym in Central Leeds',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Train stronger in central Leeds',
                'A coached strength floor, 40+ studio classes a week, and recovery rooms under one roof. Book a free trial and feel the difference in a week.',
            ),
            renderData: [
                'summary' => 'A coached strength floor, 40+ studio classes a week, and recovery rooms under one roof. Book a free trial and feel the difference in a week.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Fitness & Wellness',
                        'heading' => 'Train stronger in central Leeds',
                        'summary' => 'A coached strength floor, 40+ studio classes a week, and recovery rooms under one roof. Book a free trial and feel the difference in a week.',
                        'actions' => [
                            ['label' => 'Book a free trial', 'url' => '#trial', 'style' => 'primary'],
                            ['label' => 'View the timetable', 'url' => '#classes', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Members training on the Forge strength floor',
                    ],
                    $this->classScheduleSection(
                        heading: '40+ studio classes every week',
                        summary: 'Editorial class groupings keep the timetable premium and legible without the theme owning schedule records.',
                    ),
                    $this->coachProfilesSection(
                        heading: 'Coaches who know your goals',
                        summary: 'Specialist coach profiles pair credentials and proof so members choose the right session.',
                    ),
                    $this->membershipPlansSection(
                        heading: 'Membership built around your week',
                        summary: 'Clear tiers pair access, recovery, and coaching so members pick a plan with confidence.',
                    ),
                    $this->challengeBoardSection(),
                    $this->featuresSection(),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Start your free trial this week',
                        summary: 'A confident single-path conversion CTA turns interest into a booked first session.',
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
            name: self::BRAND . ' Classes',
            title: 'Class timetable — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Browse the full studio timetable',
                'Strength, conditioning, and recovery sessions stay scannable across the week without the theme owning class records.',
            ),
            renderData: [
                'summary' => 'Strength, conditioning, and recovery sessions stay scannable across the week without the theme owning class records.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Timetable',
                        'heading' => 'Browse the full studio timetable',
                        'summary' => 'Strength, conditioning, and recovery sessions stay scannable across the week without the theme owning class records.',
                        'actions' => [
                            ['label' => 'Book a free trial', 'url' => '#trial', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Studio class in progress',
                    ],
                    $this->classScheduleSection(
                        heading: 'Browse the full studio timetable',
                        summary: 'Strength, conditioning, and recovery sessions stay scannable across the week without the theme owning class records.',
                    ),
                    $this->coachProfilesSection(
                        heading: 'Coaches who know your goals',
                        summary: 'Specialist coach profiles pair credentials and proof so members choose the right session.',
                    ),
                    $this->featuresSection(),
                    $this->ctaSection(
                        heading: 'See a session that fits your week?',
                        summary: 'Book a free trial and try any class on the timetable with a coach to guide you.',
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
            name: self::BRAND . ' Coach',
            title: 'Meet the coach — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Meet the coach behind the session',
                'A focused coach detail pairs specialism, credentials, and proof so members book with confidence.',
            ),
            renderData: [
                'summary' => 'A focused coach detail pairs specialism, credentials, and proof so members book with confidence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Coach profile',
                        'heading' => 'Meet the coach behind the session',
                        'summary' => 'A focused coach detail pairs specialism, credentials, and proof so members book with confidence.',
                        'actions' => [
                            ['label' => 'Book a free trial', 'url' => '#trial', 'style' => 'primary'],
                            ['label' => 'View all coaches', 'url' => '#coaches', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Forge coach on the strength floor',
                    ],
                    $this->coachProfilesSection(
                        heading: 'Meet the coach behind the session',
                        summary: 'A focused coach detail pairs specialism, credentials, and proof so members book with confidence.',
                    ),
                    $this->classScheduleSection(
                        heading: '40+ studio classes every week',
                        summary: 'Editorial class groupings keep the timetable premium and legible without the theme owning schedule records.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Train with this coach',
                        summary: 'Book a free trial and put their session to the test this week.',
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
            name: self::BRAND . ' Contact',
            title: 'Talk to the team — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Talk to the team and book a free trial',
                'A non-submitting contact CTA proves the enquiry journey feels like part of the studio experience.',
            ),
            renderData: [
                'summary' => 'A non-submitting contact CTA proves the enquiry journey feels like part of the studio experience.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Contact',
                        'heading' => 'Talk to the team and book a free trial',
                        'summary' => 'Studio in central Leeds, open early until late. Email hello@forge.example or use the details below — the team replies the same day.',
                        'actions' => [
                            ['label' => 'Email the studio', 'url' => 'mailto:hello@forge.example', 'style' => 'primary'],
                            ['label' => 'View the timetable', 'url' => '#classes', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Forge studio reception',
                    ],
                    $this->ctaSection(
                        heading: 'Talk to the team and book a free trial',
                        summary: 'A non-submitting contact CTA proves the enquiry journey feels like part of the studio experience.',
                    ),
                    $this->featuresSection(),
                    $this->proofSection(),
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
            name: self::BRAND . ' No Results',
            title: 'Nothing scheduled — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing scheduled here yet',
                'An intentional empty state keeps the studio feeling premium when a listing has no records to show.',
            ),
            renderData: [
                'summary' => 'An intentional empty state keeps the studio feeling premium when a listing has no records to show.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Timetable',
                        'heading' => 'Nothing scheduled here yet',
                        'summary' => 'An intentional empty state keeps the studio feeling premium when a listing has no records to show.',
                        'actions' => [
                            ['label' => 'View the full timetable', 'url' => '#classes', 'style' => 'primary'],
                            ['label' => 'Book a free trial', 'url' => '#trial', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing scheduled here yet',
                        'summary' => 'An intentional empty state keeps the studio feeling premium when a listing has no records to show.',
                        'items' => [],
                    ],
                    $this->featuresSection(),
                    $this->ctaSection(
                        heading: 'Looking for a particular session?',
                        summary: 'Book a free trial and the team will line up the right class for your goals.',
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
                'That page took a rest day',
                'A branded 404 state guides lost members back to classes, coaches, and membership without breaking the experience.',
            ),
            renderData: [
                'summary' => 'A branded 404 state guides lost members back to classes, coaches, and membership without breaking the experience.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page took a rest day',
                        'summary' => 'A branded 404 state guides lost members back to classes, coaches, and membership without breaking the experience.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View the timetable', 'url' => '#classes', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Book a free trial and the team will point you to the right class or coach.',
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
            name: self::BRAND . ' Free Trial',
            title: 'Start your free trial — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Start your free trial this week',
                'A confident single-path conversion CTA turns interest into a booked first session.',
            ),
            renderData: [
                'summary' => 'A confident single-path conversion CTA turns interest into a booked first session.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Free trial',
                        'heading' => 'Start your free trial this week',
                        'summary' => 'A confident single-path conversion CTA turns interest into a booked first session.',
                        'actions' => [
                            ['label' => 'Book a free trial', 'url' => '#trial', 'style' => 'primary'],
                            ['label' => 'Email the studio', 'url' => 'mailto:hello@forge.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Member finishing a session at Forge',
                    ],
                    $this->ctaSection(
                        heading: 'Start your free trial this week',
                        summary: 'A confident single-path conversion CTA turns interest into a booked first session.',
                    ),
                    $this->membershipPlansSection(
                        heading: 'Membership built around your week',
                        summary: 'Clear tiers pair access, recovery, and coaching so members pick a plan with confidence.',
                    ),
                    $this->proofSection(),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function classScheduleSection(string $heading, string $summary): array
    {
        return [
            'type' => 'class-schedule',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Strength & Conditioning', 'summary' => 'Coached barbell and accessory work in small groups, Monday to Saturday across the strength floor.'],
                ['title' => 'Engine & Conditioning', 'summary' => 'High-output rowing, bike, and bodyweight intervals built to lift your aerobic ceiling.'],
                ['title' => 'Mobility & Recovery', 'summary' => 'Guided mobility, breathwork, and stretch sessions to keep you training through the week.'],
                ['title' => 'Yoga & Flow', 'summary' => 'Slow and dynamic flows in the recovery studio for balance, focus, and active rest days.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function coachProfilesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'coach-profiles',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Maya Brennan — Head of Strength', 'summary' => 'British Weightlifting coach with twelve years on the platform. Leads the coached strength floor.'],
                ['title' => 'Jordan Ellis — Conditioning Lead', 'summary' => 'Former pro rugby S&C coach. Builds the engine sessions that members come back for.'],
                ['title' => 'Priya Shah — Mobility & Recovery', 'summary' => 'Physiotherapist and mobility coach keeping members training pain-free season after season.'],
                ['title' => 'Daniel Owusu — Yoga & Flow', 'summary' => '500-hour yoga teacher pairing breathwork and movement for stronger active recovery.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function membershipPlansSection(string $heading, string $summary): array
    {
        return [
            'type' => 'membership-plans',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Off-Peak — £49/mo', 'summary' => 'Full floor and class access before 4pm. Ideal for flexible schedules and early risers.'],
                ['title' => 'Unlimited — £79/mo', 'summary' => 'Anytime access to every class, the strength floor, and the recovery rooms. No caps.'],
                ['title' => 'Coached — £129/mo', 'summary' => 'Everything in Unlimited plus two coached small-group sessions and a quarterly review.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function challengeBoardSection(): array
    {
        return [
            'type' => 'challenge-board',
            'heading' => 'This season on the challenge board',
            'summary' => 'Friendly studio-wide challenges keep members accountable and the community moving together.',
            'items' => [
                ['title' => '60-Day Strength Build', 'summary' => 'Add weight to your big lifts with a structured eight-week progression and weekly check-ins.'],
                ['title' => 'Row 100km', 'summary' => 'Bank metres across the month at your own pace and watch the leaderboard climb.'],
                ['title' => 'Consistency Streak', 'summary' => 'Hit three sessions a week for a month and earn a spot on the wall of honour.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function nutritionGuidesSection(): array
    {
        return [
            'type' => 'nutrition-guides',
            'heading' => 'Fuel the work',
            'summary' => 'Practical, coach-written nutrition guides that fit real training weeks — no fads.',
            'items' => [
                ['title' => 'Everyday Plate', 'summary' => 'A simple framework for protein, carbs, and recovery around your training days.'],
                ['title' => 'Pre & Post Training', 'summary' => 'What to eat before a heavy session and how to recover faster afterwards.'],
                ['title' => 'Eating Out', 'summary' => 'Stay on track at restaurants and on busy weeks without giving up the foods you like.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function featuresSection(): array
    {
        return [
            'type' => 'features',
            'heading' => 'Everything under one roof',
            'summary' => 'A coached floor, studio classes, and recovery rooms designed to keep you training all year.',
            'items' => [
                ['title' => 'Coached strength floor', 'summary' => 'Platforms, racks, and free weights with a coach on the floor at every session.'],
                ['title' => '40+ classes a week', 'summary' => 'Strength, conditioning, mobility, and yoga across a timetable that fits your week.'],
                ['title' => 'Recovery rooms', 'summary' => 'Sauna, contrast showers, and a dedicated mobility space to help you bounce back.'],
                ['title' => 'Central Leeds location', 'summary' => 'Minutes from the station with secure bike storage and showers for the commute.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(): array
    {
        return [
            'type' => 'proof',
            'heading' => 'Members who stay, and get stronger',
            'summary' => 'The numbers behind a coached studio members keep coming back to.',
            'items' => [
                ['title' => '40+ classes', 'summary' => 'Strength, conditioning, mobility, and yoga every single week.'],
                ['title' => '4.9 / 5 rating', 'summary' => 'Across hundreds of member reviews since the studio opened.'],
                ['title' => '1 free trial', 'summary' => 'Try the floor, a class, and the recovery rooms before you commit.'],
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
                ['label' => 'Book a free trial', 'url' => '#trial', 'style' => 'primary'],
                ['label' => 'View the timetable', 'url' => '#classes', 'style' => 'secondary'],
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
                ['label' => 'Classes', 'url' => '#classes'],
                ['label' => 'Coaches', 'url' => '#coaches'],
                ['label' => 'Membership', 'url' => '#membership'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'ctaLabel' => 'Book a free trial',
            'ctaUrl' => '#trial',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A coached strength floor, 40+ studio classes a week, and recovery rooms under one roof in central Leeds.',
            'columns' => [
                [
                    'heading' => 'Train',
                    'links' => [
                        ['label' => 'Classes', 'url' => '#classes'],
                        ['label' => 'Coaches', 'url' => '#coaches'],
                        ['label' => 'Membership', 'url' => '#membership'],
                        ['label' => 'Challenges', 'url' => '#challenges'],
                    ],
                ],
                [
                    'heading' => 'Studio',
                    'links' => [
                        ['label' => 'About', 'url' => '#studio'],
                        ['label' => 'Recovery rooms', 'url' => '#recovery'],
                        ['label' => 'Timetable', 'url' => '#classes'],
                        ['label' => 'Free trial', 'url' => '#trial'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Contact', 'url' => '#contact'],
                        ['label' => 'hello@forge.example', 'url' => 'mailto:hello@forge.example'],
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

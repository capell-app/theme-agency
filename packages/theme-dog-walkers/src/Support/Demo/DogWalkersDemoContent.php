<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DogWalkers\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Dog Walkers theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (walk-options /
 * service-areas / safety-checklist / route-board / meet-the-walkers /
 * reviews-testimonials / enquiry-form) alongside the standard hero/proof/cta —
 * giving every surface a full neighbourhood pet-care site rather than the
 * shared skeleton.
 */
final class DogWalkersDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Maple Lane Walks';

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
            title: self::BRAND . ' — Friendly Neighbourhood Dog Walking',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Reliable, friendly dog walking in your neighbourhood',
                'Maple Lane Walks is a small, trust-led team offering structured walks, calm handoffs, and proof of every visit.',
            ),
            renderData: [
                'summary' => 'A trust-led pet-care homepage for walk options, service areas, safety proof, and enquiry-led journeys.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Neighbourhood dog walking',
                        'heading' => 'Reliable, friendly dog walking in your neighbourhood',
                        'summary' => 'Structured walks, calm handoffs, and a photo report after every visit. We walk the streets you live on, with the same friendly faces each time.',
                        'actions' => [
                            ['label' => 'Request a walk', 'url' => '#enquiry'],
                            ['label' => 'See walk options', 'url' => '#walk-options'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'A dog walker with two dogs on a leafy street',
                    ],
                    $this->walkOptionsSection(
                        heading: 'Walk options local owners can compare at a glance',
                        summary: 'Pick the rhythm that suits your dog. Every walk is small-group or solo, GPS-tracked, and finished with a quick report home.',
                    ),
                    $this->serviceAreasSection(
                        heading: 'The neighbourhoods we cover, proven up front',
                        summary: 'We keep routes tight so we are never more than a few minutes from your door.',
                    ),
                    $this->safetyChecklistSection(),
                    $this->routeBoardSection(
                        heading: 'Route proof and visit checks owners can rely on',
                        summary: 'Every walk lands with a GPS route, a check-in note, and a photo — so you always know how the day went.',
                    ),
                    $this->meetTheWalkersSection(),
                    $this->reviewsSection(),
                    $this->proofSection(
                        heading: 'Trusted by local owners, walk after walk',
                        summary: 'The numbers behind a calm, dependable service.',
                    ),
                    $this->enquirySection(
                        heading: 'Request a walk through one calm enquiry path',
                        summary: 'Tell us about your dog and your week. We reply the same day with availability.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to book your first walk?',
                        summary: 'Most new dogs start within the week. Send a quick enquiry and we will sort the rest.',
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
            name: self::BRAND . ' Walk Options',
            title: 'Walk options — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Walk options and service areas',
                'A directory of walk types and the neighbourhoods we cover, with the enquiry path one tap away.',
            ),
            renderData: [
                'summary' => 'Compare walk types and the neighbourhoods we cover, with the enquiry path one tap away.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Walk options',
                        'heading' => 'Find the right walk for your dog',
                        'summary' => 'Solo strolls for the shy, small-group adventures for the social, and puppy visits for the little ones. Browse the options and request a slot.',
                        'actions' => [
                            ['label' => 'Request a walk', 'url' => '#enquiry'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Dogs on a group walk in the park',
                    ],
                    $this->walkOptionsSection(
                        heading: 'Compare every walk type',
                        summary: 'Clear pricing, clear group sizes, and a report after every visit.',
                    ),
                    $this->serviceAreasSection(
                        heading: 'Neighbourhoods we cover',
                        summary: 'Find your postcode and see the routes we already run nearby.',
                    ),
                    $this->resourcesSection(
                        heading: 'Pet-care advice from the team',
                        summary: 'Practical guides for settling a new dog, recall, and rainy-day routines.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'See a walk that suits your dog?',
                        summary: 'Send a quick enquiry and we will confirm availability the same day.',
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
            name: self::BRAND . ' Walk Detail',
            title: 'Small-group adventure walk — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Small-group adventure walk',
                'A 60-minute small-group walk for social dogs, with GPS tracking and a photo report home.',
            ),
            renderData: [
                'summary' => 'A 60-minute small-group walk for social dogs, with GPS tracking and a photo report home.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Walk detail',
                        'heading' => 'Small-group adventure walk',
                        'summary' => 'Sixty minutes of off-lead-ready fun for sociable dogs, in groups of four or fewer. Routes rotate through the woods, the common, and the riverside path.',
                        'actions' => [
                            ['label' => 'Request this walk', 'url' => '#enquiry'],
                            ['label' => 'See all options', 'url' => '#walk-options'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'A small group of dogs on an adventure walk',
                    ],
                    $this->routeBoardSection(
                        heading: 'What a typical adventure walk looks like',
                        summary: 'A GPS route, a mid-walk check-in, and a photo at the finish.',
                    ),
                    $this->safetyChecklistSection(),
                    $this->meetTheWalkersSection(),
                    $this->reviewsSection(),
                    $this->ctaSection(
                        heading: 'Think this walk suits your dog?',
                        summary: 'Tell us about your dog and we will match them to the right group.',
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
            name: self::BRAND . ' Enquiry',
            title: 'Request a walk — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Request a walk',
                'Tell us about your dog and your week. We reply the same day with availability.',
            ),
            renderData: [
                'summary' => 'Tell us about your dog and your week. We reply the same day with availability.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Enquiry',
                        'heading' => 'Request a walk',
                        'summary' => 'Based in Maple Lane, covering the surrounding postcodes. Call, email, or send the enquiry form below — we reply the same working day.',
                        'actions' => [
                            ['label' => 'Email the team', 'url' => 'mailto:hello@maplelanewalks.example'],
                            ['label' => 'See walk options', 'url' => '#walk-options'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'A friendly dog walker greeting a dog at the door',
                    ],
                    $this->enquirySection(
                        heading: 'Request a walk through one calm enquiry path',
                        summary: 'Share your dog\'s name, your postcode, and the days you need. We do the rest.',
                    ),
                    $this->contactSection(),
                    $this->openingHoursSection(),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through?',
                        summary: 'Give us a call and we will walk you through how a first visit works.',
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
            name: self::BRAND . ' No Results',
            title: 'No walks match — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No walks match that filter yet',
                'A graceful empty state for a filtered walk listing with no matching routes.',
            ),
            renderData: [
                'summary' => 'No walks match that filter yet — but the team can still point you somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Walk listing',
                        'heading' => 'No walks match that filter — yet',
                        'summary' => 'We do not run a route in that area on those days right now. Clear the filter to see everything, or tell us what you need.',
                        'actions' => [
                            ['label' => 'See all walks', 'url' => '#walk-options'],
                            ['label' => 'Request a walk', 'url' => '#enquiry'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing to show here yet',
                        'summary' => 'When we add a route in this area it will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->walkOptionsSection(
                        heading: 'While you are here, see what we offer',
                        summary: 'The walk types local owners book most.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific route?',
                        summary: 'Tell us your postcode and days and we will see what we can arrange.',
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
                'Page not found',
                'A not-found page that routes visitors back into walk options and the enquiry path.',
            ),
            renderData: [
                'summary' => 'That page has wandered off the lead — here is the way back.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page wandered off the lead',
                        'summary' => 'The link is broken or the page has moved. Head back to the walk options, or send the team an enquiry.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/'],
                            ['label' => 'See walk options', 'url' => '#walk-options'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and we will point you to the right place.',
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
            name: self::BRAND . ' Book',
            title: 'Book your first walk — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Ready to book your first walk?',
                'A focused conversion page inviting new walk enquiries.',
            ),
            renderData: [
                'summary' => 'Ready to book your first walk? Start with a quick enquiry.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => "Let's get walking",
                        'heading' => 'Ready to book your first walk?',
                        'summary' => 'Whether it is a daily routine or the occasional adventure, your dog gets the same calm, dependable team every time.',
                        'actions' => [
                            ['label' => 'Request a walk', 'url' => '#enquiry'],
                            ['label' => 'Email the team', 'url' => 'mailto:hello@maplelanewalks.example'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'A happy dog after a walk',
                    ],
                    $this->proofSection(
                        heading: 'Why local owners choose Maple Lane',
                        summary: 'The numbers behind a calm, dependable service.',
                    ),
                    $this->enquirySection(
                        heading: 'Request a walk through one calm enquiry path',
                        summary: 'Tell us about your dog and your week. We reply the same day.',
                    ),
                    $this->ctaSection(
                        heading: 'One enquiry away',
                        summary: 'Send the details over and we will come back the same working day with next steps.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function walkOptionsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'walk-options',
            'heading' => $heading,
            'summary' => $summary,
            'mobileLayout' => 'stack',
            'items' => [
                [
                    'category' => 'Daily',
                    'title' => 'Solo neighbourhood stroll',
                    'summary' => 'A calm 30-minute one-to-one walk, ideal for shy dogs, seniors, or pups still learning the ropes.',
                    'price' => 'From £14',
                ],
                [
                    'category' => 'Most popular',
                    'title' => 'Small-group adventure walk',
                    'summary' => 'Sixty minutes with three friendly dogs or fewer, rotating through woods, common, and riverside routes.',
                    'price' => 'From £18',
                ],
                [
                    'category' => 'Puppies',
                    'title' => 'Puppy visit & garden play',
                    'summary' => 'A gentle 20-minute drop-in with toilet breaks, fresh water, and short lead practice at home.',
                    'price' => 'From £12',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceAreasSection(string $heading, string $summary): array
    {
        return [
            'type' => 'service-areas',
            'heading' => $heading,
            'summary' => $summary,
            'mobileLayout' => 'stack',
            'items' => [
                ['label' => 'Maple Lane & Oakfield', 'postcode' => 'ML1', 'url' => '#area-maple-lane'],
                ['label' => 'Riverside & The Common', 'postcode' => 'ML2', 'url' => '#area-riverside'],
                ['label' => 'Hillcrest & Beech Park', 'postcode' => 'ML3', 'url' => '#area-hillcrest'],
                ['label' => 'Station Road & Old Town', 'postcode' => 'ML4', 'url' => '#area-old-town'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function safetyChecklistSection(): array
    {
        return [
            'type' => 'safety-checklist',
            'heading' => 'Insured, checked, and trained for calm walks',
            'summary' => 'Every walker is vetted, first-aid trained, and fully insured before they ever hold a lead.',
            'badges' => [
                ['title' => 'Fully insured', 'summary' => 'Public liability and care, custody & control cover on every walk.', 'issuer' => 'Pet Business Insurance'],
                ['title' => 'Canine first aid', 'summary' => 'Every walker holds a current canine first-aid certificate.', 'issuer' => 'iPET Network'],
                ['title' => 'DBS checked', 'summary' => 'Enhanced background checks for everyone with a door key.', 'issuer' => 'DBS'],
                ['title' => 'GPS tracked', 'summary' => 'Live route tracking on every walk, shared in your report.', 'issuer' => 'Maple Lane Walks'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function routeBoardSection(string $heading, string $summary): array
    {
        return [
            'type' => 'route-board',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['type' => 'Pickup', 'title' => 'Calm collection from your door', 'summary' => 'We arrive in the agreed window, use your handoff routine, and lock up exactly as asked.'],
                ['type' => 'On the walk', 'title' => 'GPS route & mid-walk check-in', 'summary' => 'You can see the live route, plus a quick note halfway through if anything needs flagging.'],
                ['type' => 'Home', 'title' => 'Photo report after every visit', 'summary' => 'A short report lands when your dog is home: fresh water, toilet breaks, and a happy photo.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function meetTheWalkersSection(): array
    {
        return [
            'type' => 'meet-the-walkers',
            'heading' => 'Meet the people who walk your dog',
            'summary' => 'A small, consistent team — your dog sees the same friendly faces each week.',
            'mobileLayout' => 'stack',
            'items' => [
                ['metric' => '8 years', 'title' => 'Priya, lead walker', 'summary' => 'Founded Maple Lane Walks and still walks every day. Calm with nervous and reactive dogs.'],
                ['metric' => '5 years', 'title' => 'Tom, weekday walker', 'summary' => 'Runs the small-group adventure routes. First-aid trained and endlessly patient.'],
                ['metric' => '3 years', 'title' => 'Lena, puppy specialist', 'summary' => 'Handles puppy visits and settling-in walks for new arrivals.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function reviewsSection(): array
    {
        return [
            'type' => 'reviews-testimonials',
            'heading' => 'What local owners say',
            'summary' => 'Reviews from owners across the neighbourhoods we cover.',
            'items' => [
                ['rating' => 5, 'quote' => 'Our rescue was so anxious. Priya took it slow and now he waits at the window for his walk.', 'author' => 'Hannah, Maple Lane'],
                ['rating' => 5, 'quote' => 'The photo reports make my day. I always know exactly how the walk went.', 'author' => 'Daniel, Riverside'],
                ['rating' => 5, 'quote' => 'Reliable, friendly, and genuinely good with dogs. Never had a single worry.', 'author' => 'Sofia, Hillcrest'],
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
            'mobileLayout' => 'stack',
            'items' => [
                ['metric' => '6,000+', 'name' => 'Walks completed', 'quote' => 'Thousands of calm, tracked walks across the neighbourhood.'],
                ['metric' => 'Same day', 'name' => 'Enquiry replies', 'quote' => 'You hear back from a real person, fast.'],
                ['metric' => '4.9/5', 'name' => 'Average owner rating', 'quote' => 'From local reviews across every route we run.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function enquirySection(string $heading, string $summary): array
    {
        return [
            'type' => 'enquiry-form',
            'heading' => $heading,
            'summary' => $summary,
            'formAction' => '/contact',
            'formMethod' => 'POST',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function openingHoursSection(): array
    {
        return [
            'type' => 'opening-hours',
            'heading' => 'When we walk',
            'summary' => 'Walk windows run through the day, with enquiries answered the same working day.',
            'openNow' => true,
            'items' => [
                ['label' => 'Monday – Friday', 'value' => '7:00 – 18:00'],
                ['label' => 'Saturday', 'value' => '8:00 – 16:00'],
                ['label' => 'Sunday', 'value' => 'By arrangement'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contactSection(): array
    {
        return [
            'type' => 'contact',
            'heading' => 'How to reach the team',
            'summary' => 'Call, email, or drop by — whatever suits you.',
            'phone' => '01234 567 890',
            'email' => 'hello@maplelanewalks.example',
            'address' => '14 Maple Lane, Oakfield, ML1 2AB',
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function resourcesSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['title' => 'Settling a rescue into a walk routine', 'category' => 'Guide', 'summary' => 'Slow introductions and short first walks that build trust without overwhelm.'],
            ['title' => 'Recall in the park, step by step', 'category' => 'Training', 'summary' => 'How we build reliable recall on group adventure walks.'],
            ['title' => 'Rainy-day routines for high-energy dogs', 'category' => 'Wellbeing', 'summary' => 'Keeping dogs happy and tired when the weather turns.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#resource-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
            ];
        }

        return [
            'type' => 'resources',
            'heading' => $heading,
            'summary' => $summary,
            'mobileLayout' => 'stack',
            'items' => $items,
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
                ['label' => 'Request a walk', 'url' => '#enquiry'],
                ['label' => 'See walk options', 'url' => '#walk-options'],
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
                ['label' => 'Walk options', 'url' => '#walk-options'],
                ['label' => 'Service areas', 'url' => '#service-areas'],
                ['label' => 'Route board', 'url' => '#route-board'],
                ['label' => 'Reviews', 'url' => '#reviews'],
                ['label' => 'Enquiry', 'url' => '#enquiry'],
            ],
            'ctaLabel' => 'Request a walk',
            'ctaUrl' => '#enquiry',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A small, trust-led dog walking team. Maple Lane and the surrounding neighbourhoods.',
            'columns' => [
                [
                    'heading' => 'Walks',
                    'links' => [
                        ['label' => 'Solo strolls', 'url' => '#walk-options'],
                        ['label' => 'Adventure walks', 'url' => '#walk-options'],
                        ['label' => 'Puppy visits', 'url' => '#walk-options'],
                        ['label' => 'Service areas', 'url' => '#service-areas'],
                    ],
                ],
                [
                    'heading' => 'Trust',
                    'links' => [
                        ['label' => 'Safety & insurance', 'url' => '#safety'],
                        ['label' => 'Route board', 'url' => '#route-board'],
                        ['label' => 'Meet the walkers', 'url' => '#walkers'],
                        ['label' => 'Reviews', 'url' => '#reviews'],
                    ],
                ],
                [
                    'heading' => 'Contact',
                    'links' => [
                        ['label' => 'Request a walk', 'url' => '#enquiry'],
                        ['label' => 'hello@maplelanewalks.example', 'url' => 'mailto:hello@maplelanewalks.example'],
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

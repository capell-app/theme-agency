<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Healthcare\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Healthcare theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (service-finder / services /
 * care-pathway / clinicians / booking / events / blog-teaser / contact) alongside
 * the standard hero/proof/cta — giving every surface a full, appointment-led
 * clinical website rather than the shared five-section skeleton.
 */
final class HealthcareDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Meridian Clinics';

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
            title: self::BRAND . ' — Appointment-Led Clinical Care',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Appointment-led care a clinic can stand behind',
                'Meridian Clinics is a private healthcare group helping patients find the right service, the right clinician, and the right appointment with calm, clinical confidence.',
            ),
            renderData: [
                'summary' => 'Meridian Clinics is a private healthcare group. Discover services, meet our clinicians, follow a clear care pathway, and book an appointment at the location nearest you.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Healthcare',
                        'heading' => 'Appointment-led care a clinic can stand behind',
                        'summary' => 'A trust-building front door for service discovery, clinicians, care pathways, locations, and booking-led journeys — built around the patient, not the paperwork.',
                        'actions' => [
                            ['label' => 'Book an appointment', 'url' => '#booking', 'style' => 'primary'],
                            ['label' => 'Find a service', 'url' => '#services', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Meridian Clinics care team',
                    ],
                    $this->serviceFinderSection(
                        heading: 'Find the right care in a few clear steps',
                        summary: 'Structured care-route cards keep clinical options legible and appointment-led, so patients reach the right service without the guesswork.',
                    ),
                    $this->servicesSection(
                        heading: 'Services across every stage of care',
                        summary: 'From first consultation to follow-up, each service is delivered by a dedicated clinical team.',
                        media: $media,
                    ),
                    $this->carePathwaySection(
                        heading: 'A care pathway patients can follow with confidence',
                        summary: 'Three clear stages from first enquiry to ongoing care, so patients always know what happens next.',
                    ),
                    $this->cliniciansSection(
                        heading: 'Meet the clinicians behind your care',
                        summary: 'A senior, multidisciplinary team — every appointment is with a named specialist.',
                        media: $media,
                    ),
                    $this->bookingSection(
                        heading: 'Booking that feels like part of the care',
                        summary: 'Tell us the service and a preferred time. The booking panel lights up live when Capell Bookings and Form Builder are installed.',
                    ),
                    $this->eventsSection(
                        heading: 'Clinics, screenings, and patient events',
                        summary: 'Upcoming health screenings and open clinics. The events list lights up automatically when Capell Events is installed.',
                    ),
                    $this->proofSection(
                        heading: 'Outcomes patients and referrers trust',
                        summary: 'The clinical signals behind the care, from satisfaction to safety review.',
                    ),
                    $this->blogTeaserSection(
                        heading: 'Guidance from our clinical team',
                        summary: 'Patient-friendly resources written and reviewed by the clinicians who deliver the care.',
                        media: $media,
                    ),
                    $this->contactSection(
                        heading: 'Reach the clinic through one clear path',
                        summary: 'Speak to the patient team at the location nearest you, or start an enquiry online.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to book an appointment?',
                        summary: 'Start an enquiry and the patient team will confirm your appointment within one working day.',
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
            name: self::BRAND . ' Services',
            title: 'Services — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Services for every stage of care',
                'Browse the full range of clinical services at Meridian Clinics, grouped by care route and delivered by named specialists.',
            ),
            renderData: [
                'summary' => 'Every clinical service at Meridian Clinics, grouped by care route and delivered by a dedicated specialist team.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Our services',
                        'heading' => 'Services for every stage of care',
                        'summary' => 'Filter by care route, then book directly with the clinical team that delivers it.',
                        'actions' => [
                            ['label' => 'Book an appointment', 'url' => '#booking', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Meridian Clinics services',
                    ],
                    $this->serviceFinderSection(
                        heading: 'A service finder built to route patients to the right care',
                        summary: 'Structured care-route cards keep clinical options legible and appointment-led without the theme owning service records.',
                    ),
                    $this->servicesSection(
                        heading: 'Featured services',
                        summary: 'The services patients ask for most, each with its own clinical team.',
                        media: $media,
                    ),
                    $this->carePathwaySection(
                        heading: 'How a typical visit works',
                        summary: 'The same clear pathway applies to every service, so patients always know what to expect.',
                    ),
                    $this->ctaSection(
                        heading: 'Found the service you need?',
                        summary: 'Start an enquiry and the patient team will match you with the right clinician and a convenient time.',
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
            name: self::BRAND . ' Clinician',
            title: 'Dr Amara Okafor — Clinician — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Dr Amara Okafor — Consultant Cardiologist',
                'A clinician profile that pairs expertise and proof so patients can book the right appointment with confidence.',
            ),
            renderData: [
                'summary' => 'Meet Dr Amara Okafor, Consultant Cardiologist — her credentials, specialties, and the proof behind the care.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Clinician',
                        'heading' => 'A clinician profile that reads with clinical confidence',
                        'summary' => 'A single clinician view pairs expertise and proof so patients can book the right appointment with confidence.',
                        'actions' => [
                            ['label' => 'Book with this clinician', 'url' => '#booking', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Dr Amara Okafor',
                    ],
                    $this->clinicianProfileSection($media),
                    $this->insuranceTrustSection(
                        heading: 'Cover, accreditation, and the safety behind the care',
                        summary: 'The trust signals patients and referrers look for, from recognised insurers to clinical governance.',
                    ),
                    $this->proofSection(
                        heading: 'The numbers behind this clinic',
                        summary: 'Outcomes and safety signals from the cardiology service.',
                    ),
                    $this->ctaSection(
                        heading: 'Want to book with Dr Okafor?',
                        summary: 'Start an enquiry and the patient team will confirm the next available appointment.',
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
            title: 'Contact & booking — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Reach the clinic through one clear path',
                'Speak to the patient team, find your nearest location, or start a booking enquiry — every route leads to the same calm clinical experience.',
            ),
            renderData: [
                'summary' => 'Speak to the patient team, find your nearest location, or start a booking enquiry. Every route leads to the same calm clinical experience.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Contact',
                        'heading' => 'Reach the clinic through one clear appointment path',
                        'summary' => 'Call the patient team on 0800 123 4567, or use the booking panel below — we confirm every enquiry within one working day.',
                        'actions' => [
                            ['label' => 'Call the patient team', 'url' => 'tel:08001234567', 'style' => 'primary'],
                            ['label' => 'Find a service', 'url' => '#services', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Meridian Clinics reception',
                    ],
                    $this->contactSection(
                        heading: 'Reach the clinic through one clear appointment path',
                        summary: 'A non-submitting contact panel proves the enquiry journey feels like part of the clinical experience.',
                    ),
                    $this->locationsSection(
                        heading: 'Find your nearest clinic',
                        summary: 'Three locations across the region, each with its own patient team and opening hours.',
                    ),
                    $this->bookingSection(
                        heading: 'Start a booking enquiry',
                        summary: 'Tell us the service and a preferred time and the patient team will take it from there.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to book an appointment?',
                        summary: 'Start an enquiry and the patient team will confirm your appointment within one working day.',
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
            title: 'No services match — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No services match that filter yet',
                'A graceful empty state for a filtered service finder with no matching care routes.',
            ),
            renderData: [
                'summary' => 'No services match that filter yet — but the patient team can still point you to the right care.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Service finder',
                        'heading' => 'No services match that filter — yet',
                        'summary' => 'Clear the filter to see every service, or talk to the patient team and we will guide you to the right care route.',
                        'actions' => [
                            ['label' => 'View all services', 'url' => '#services', 'style' => 'primary'],
                            ['label' => 'Book an appointment', 'url' => '#booking', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'services',
                        'heading' => 'Nothing to show here',
                        'summary' => 'When services land in this care route they will appear here, ready to book.',
                        'features' => [],
                    ],
                    $this->serviceFinderSection(
                        heading: 'While you are here',
                        summary: 'Browse by care route to find the service that fits.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific service?',
                        summary: 'Tell the patient team what you need and we will point you to the right clinic.',
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
                'A not-found page that routes patients back into the services and booking paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back to care.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page is no longer in our records',
                        'summary' => 'The link is broken or the page has moved. Head back to our services, or start a booking enquiry with the patient team.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Find a service', 'url' => '#services', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for care?',
                        summary: 'Tell the patient team what you needed and we will route you to the right clinic.',
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
            title: 'Book an appointment — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Ready to put your care first?',
                'A focused conversion page inviting patients to start a booking enquiry.',
            ),
            renderData: [
                'summary' => 'Ready to put your care first? Start a booking enquiry with the Meridian Clinics patient team.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Book an appointment',
                        'heading' => 'Ready to put your care first?',
                        'summary' => 'Whether it is a first consultation or ongoing care, you are seen by a named specialist and supported by the same patient team throughout.',
                        'actions' => [
                            ['label' => 'Book an appointment', 'url' => '#booking', 'style' => 'primary'],
                            ['label' => 'Call the patient team', 'url' => 'tel:08001234567', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Meridian Clinics patient team',
                    ],
                    $this->proofSection(
                        heading: 'Why patients choose Meridian Clinics',
                        summary: 'The clinical signals behind the care.',
                    ),
                    $this->bookingSection(
                        heading: 'Start your booking enquiry',
                        summary: 'Tell us the service and a preferred time — the patient team will confirm within one working day.',
                    ),
                    $this->ctaSection(
                        heading: 'One enquiry away from care',
                        summary: 'Send your details over and we will come back within one working day with your appointment.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceFinderSection(string $heading, string $summary): array
    {
        return [
            'type' => 'service-finder',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'group' => 'By care route',
                    'options' => ['Cardiology', 'Dermatology', 'Orthopaedics', 'Women\'s health', 'GP & diagnostics'],
                ],
                [
                    'group' => 'By appointment type',
                    'options' => ['First consultation', 'Follow-up', 'Diagnostic test', 'Minor procedure'],
                ],
                [
                    'group' => 'By location',
                    'options' => ['City Centre', 'Riverside', 'Northgate'],
                ],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function servicesSection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['cta'],
        )));

        $services = [
            ['title' => 'Cardiology', 'type' => 'Consultation', 'summary' => 'Heart health assessments, diagnostics, and ongoing care led by consultant cardiologists.', 'metric' => 'From £180'],
            ['title' => 'Dermatology', 'type' => 'Consultation', 'summary' => 'Skin checks, mole mapping, and treatment plans with same-week appointments.', 'metric' => 'From £150'],
            ['title' => 'Orthopaedics', 'type' => 'Consultation', 'summary' => 'Joint, bone, and sports injury care from diagnosis through to rehabilitation.', 'metric' => 'From £200'],
            ['title' => 'Women\'s health', 'type' => 'Consultation', 'summary' => 'Gynaecology, screening, and wellbeing reviews in a calm, private setting.', 'metric' => 'From £170'],
            ['title' => 'GP & diagnostics', 'type' => 'Appointment', 'summary' => 'Private GP appointments with on-site blood tests and rapid imaging.', 'metric' => 'From £120'],
        ];

        $items = [];

        foreach ($services as $index => $service) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$service,
                'url' => '#service-' . ($index + 1),
                'image' => $image,
                'imageAlt' => $service['title'] . ' service',
            ];
        }

        return [
            'type' => 'services',
            'heading' => $heading,
            'summary' => $summary,
            'features' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function carePathwaySection(string $heading, string $summary): array
    {
        return [
            'type' => 'care-pathway',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'type' => 'Step 1',
                    'title' => 'Enquiry & triage',
                    'summary' => 'Tell us what you need. The patient team matches you to the right service and clinician, usually within one working day.',
                ],
                [
                    'type' => 'Step 2',
                    'title' => 'Consultation & diagnostics',
                    'summary' => 'Meet your named specialist. On-site diagnostics mean tests and imaging often happen the same day.',
                ],
                [
                    'type' => 'Step 3',
                    'title' => 'Treatment & follow-up',
                    'summary' => 'A clear treatment plan with scheduled follow-ups, so your care never loses momentum.',
                ],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function cliniciansSection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge($media['detail'], $media['listing'], $media['proof'])));

        $clinicians = [
            ['title' => 'Dr Amara Okafor', 'type' => 'Consultant Cardiologist', 'summary' => 'Twenty years in interventional cardiology, leading the heart health service.'],
            ['title' => 'Dr Leah Berman', 'type' => 'Consultant Dermatologist', 'summary' => 'Skin cancer screening and complex dermatology, with a calm, patient-first manner.'],
            ['title' => 'Mr Daniel Frost', 'type' => 'Orthopaedic Surgeon', 'summary' => 'Sports injury and joint preservation specialist for active patients.'],
            ['title' => 'Dr Priya Nair', 'type' => 'Private GP', 'summary' => 'Whole-person primary care with rapid access to on-site diagnostics.'],
        ];

        $items = [];

        foreach ($clinicians as $index => $clinician) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$clinician,
                'url' => '#clinician-' . ($index + 1),
                'image' => $image,
                'imageAlt' => $clinician['title'],
            ];
        }

        return [
            'type' => 'clinicians',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingSection(string $heading, string $summary): array
    {
        return [
            'type' => 'booking',
            'heading' => $heading,
            'summary' => $summary,
            'phone' => '0800 123 4567',
            'contactUrl' => '#contact',
            'primaryAction' => ['label' => 'Start a booking enquiry', 'url' => '#contact'],
            'items' => [
                ['title' => 'Same-week appointments'],
                ['title' => 'Named specialist every time'],
                ['title' => 'On-site diagnostics'],
                ['title' => 'Recognised by major insurers'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function eventsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'events',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['date' => 'Mon 14 Jul', 'title' => 'Heart health open clinic', 'summary' => 'Free blood-pressure checks and a talk from our cardiology team.', 'url' => '#event-1'],
                ['date' => 'Sat 26 Jul', 'title' => 'Skin cancer screening day', 'summary' => 'Walk-in mole checks with our consultant dermatologists.', 'url' => '#event-2'],
                ['date' => 'Wed 6 Aug', 'title' => 'Women\'s wellbeing evening', 'summary' => 'An informal evening on screening, menopause, and preventive care.', 'url' => '#event-3'],
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
                ['name' => 'Patient satisfaction', 'metric' => '98%', 'summary' => 'Patients who would recommend Meridian Clinics to friends and family.'],
                ['name' => 'Time to appointment', 'metric' => '3 days', 'summary' => 'Typical wait for a first consultation across our services.'],
                ['name' => 'Safety reviews', 'metric' => '100%', 'summary' => 'Procedures covered by our clinical governance and safety review process.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function blogTeaserSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $articles = [
            ['title' => 'Understanding your blood pressure numbers', 'type' => 'Heart health', 'summary' => 'A plain-English guide to what your readings mean and when to act.'],
            ['title' => 'When to get a mole checked', 'type' => 'Skin health', 'summary' => 'The simple ABCDE rule our dermatologists use, explained for patients.'],
            ['title' => 'Recovering well after a joint injury', 'type' => 'Orthopaedics', 'summary' => 'What good rehabilitation looks like and how to protect your progress.'],
        ];

        $items = [];

        foreach ($articles as $index => $article) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$article,
                'url' => '#resource-' . ($index + 1),
                'imageUrl' => $image,
                'imageAlt' => $article['title'],
            ];
        }

        return [
            'type' => 'blog-teaser',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contactSection(string $heading, string $summary): array
    {
        return [
            'type' => 'contact',
            'heading' => $heading,
            'summary' => $summary,
            'locations' => [
                [
                    'label' => 'Flagship clinic',
                    'title' => 'Meridian City Centre',
                    'summary' => 'Full-service clinic with on-site diagnostics and minor procedures.',
                    'address' => '12 Castle Street, City Centre, BR1 2AB',
                    'hours' => 'Mon–Fri 8am–8pm, Sat 9am–4pm',
                    'phone' => '0800 123 4567',
                ],
                [
                    'label' => 'Community clinic',
                    'title' => 'Meridian Riverside',
                    'summary' => 'Consultations and follow-up appointments by the river.',
                    'address' => '4 Wharf Lane, Riverside, BR3 5GH',
                    'hours' => 'Mon–Fri 9am–6pm',
                    'phone' => '0800 123 4568',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function locationsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'locations',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'type' => 'Flagship clinic',
                    'title' => 'Meridian City Centre',
                    'summary' => 'Our largest clinic, with the full range of services and on-site imaging.',
                    'address' => '12 Castle Street, City Centre, BR1 2AB',
                    'hours' => 'Mon–Fri 8am–8pm, Sat 9am–4pm',
                    'phone' => '0800 123 4567',
                ],
                [
                    'type' => 'Community clinic',
                    'title' => 'Meridian Riverside',
                    'summary' => 'Consultations and follow-up care in a quieter riverside setting.',
                    'address' => '4 Wharf Lane, Riverside, BR3 5GH',
                    'hours' => 'Mon–Fri 9am–6pm',
                    'phone' => '0800 123 4568',
                ],
                [
                    'type' => 'Diagnostic centre',
                    'title' => 'Meridian Northgate',
                    'summary' => 'Dedicated diagnostics — bloods, imaging, and rapid results.',
                    'address' => '88 Northgate Road, Northgate, BR7 9LP',
                    'hours' => 'Mon–Sat 7am–7pm',
                    'phone' => '0800 123 4569',
                ],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function clinicianProfileSection(array $media): array
    {
        return [
            'type' => 'clinician-profile',
            'heading' => 'Dr Amara Okafor — Consultant Cardiologist',
            'summary' => 'Dr Okafor leads the heart health service at Meridian Clinics. With twenty years in interventional cardiology, she combines rigorous diagnostics with a calm, patient-first approach to ongoing care.',
            'image' => $media['detail'][0] ?? null,
            'imageAlt' => 'Dr Amara Okafor, Consultant Cardiologist',
            'credentials' => ['MBBS', 'MRCP', 'MD (Cardiology)', 'GMC registered'],
            'specialties' => ['Interventional cardiology', 'Heart failure', 'Preventive cardiology', 'Cardiac imaging'],
            'languages' => ['English', 'French', 'Yoruba'],
            'availability' => 'Accepting new patients — next available within one week',
            'acceptingPatients' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function insuranceTrustSection(string $heading, string $summary): array
    {
        return [
            'type' => 'insurance-trust',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['type' => 'Insurance', 'title' => 'Recognised by major insurers', 'summary' => 'We work with the leading private medical insurers and offer transparent self-pay pricing.'],
                ['type' => 'Accreditation', 'title' => 'Independently regulated', 'summary' => 'Inspected and rated by the national care regulator, with results published openly.'],
                ['type' => 'Governance', 'title' => 'Clinical safety review', 'summary' => 'Every procedure is covered by our clinical governance and safety review process.'],
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
                ['label' => 'Book an appointment', 'url' => '#booking', 'style' => 'primary'],
                ['label' => 'Find a service', 'url' => '#services', 'style' => 'secondary'],
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
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Clinicians', 'url' => '#clinicians'],
                ['label' => 'Locations', 'url' => '#locations'],
                ['label' => 'Booking', 'url' => '#booking'],
            ],
            'ctaLabel' => 'Book an appointment',
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
            'summary' => 'A private healthcare group built around appointment-led, patient-first care.',
            'columns' => [
                [
                    'heading' => 'Care',
                    'links' => [
                        ['label' => 'Services', 'url' => '#services'],
                        ['label' => 'Clinicians', 'url' => '#clinicians'],
                        ['label' => 'Care pathways', 'url' => '#services'],
                        ['label' => 'Events', 'url' => '#events'],
                    ],
                ],
                [
                    'heading' => 'Visit',
                    'links' => [
                        ['label' => 'Locations', 'url' => '#locations'],
                        ['label' => 'Opening hours', 'url' => '#locations'],
                        ['label' => 'Insurance & cover', 'url' => '#services'],
                        ['label' => 'Booking', 'url' => '#booking'],
                    ],
                ],
                [
                    'heading' => 'Contact',
                    'links' => [
                        ['label' => 'Patient team', 'url' => '#contact'],
                        ['label' => '0800 123 4567', 'url' => 'tel:08001234567'],
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

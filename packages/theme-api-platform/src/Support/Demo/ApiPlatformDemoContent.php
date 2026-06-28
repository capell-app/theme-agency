<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ApiPlatform\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the API Platform theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (code-hero / quickstart /
 * api-reference-teaser / sdk-grid / status-uptime) alongside the standard
 * hero/features/proof/cta — giving every surface a full, individual developer
 * platform site rather than the shared five-section skeleton.
 */
final class ApiPlatformDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Northwind API';

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
            title: self::BRAND . ' — Payments and Identity, One REST API',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A developer-first API platform',
                'Northwind API gives engineering teams payments, identity, and webhooks behind one REST surface — with idempotent requests, typed SDKs, and a 99.99% uptime record.',
            ),
            renderData: [
                'summary' => 'Northwind API ships payments, identity, and webhooks behind one versioned REST surface, with idempotency keys, signed events, official SDKs, and a public status page.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Developer platform',
                        'heading' => 'Ship payments and identity on one REST API',
                        'summary' => 'Northwind exposes payments, customers, and webhooks behind a single versioned endpoint. Authenticate with a bearer token, send your first idempotent request in under a minute, and go live without rewiring your stack.',
                        'actions' => [
                            ['label' => 'Read the quickstart', 'url' => '#quickstart', 'style' => 'primary'],
                            ['label' => 'Browse the API reference', 'url' => '#api-reference', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Northwind API developer dashboard',
                    ],
                    $this->codeHeroSection(),
                    $this->quickstartSection(
                        heading: 'From key to first request in four steps',
                        summary: 'A copy-paste path from a fresh API key to a live response — no SDK required, though one is waiting if you want it.',
                    ),
                    $this->apiReferenceSection(
                        heading: 'A reference you can read top to bottom',
                        summary: 'Every resource documents its verbs, parameters, and error shapes with runnable examples in five languages.',
                    ),
                    $this->featuresSection(
                        heading: 'Built for engineers who run this in production',
                        summary: 'The platform primitives teams reach for first when an integration has to survive real traffic.',
                    ),
                    $this->sdkGridSection(
                        heading: 'Official SDKs for the stack you already use',
                        summary: 'Typed clients with retries, pagination, and idempotency built in — versioned in lockstep with the API.',
                    ),
                    $this->statusUptimeSection(
                        heading: 'Reliability you can put on a status page',
                        summary: 'Real numbers from the last 90 days, published openly and updated in real time.',
                    ),
                    $this->proofSection(
                        heading: 'Trusted by teams shipping at scale',
                        summary: 'The platform already runs in production for fintechs, marketplaces, and SaaS teams.',
                    ),
                    $this->ctaSection(
                        heading: 'Make your first call today',
                        summary: 'Grab a test key, send a request against the sandbox, and flip to live keys when you are ready. No sales call required.',
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
            name: self::BRAND . ' API Reference',
            title: 'API Reference — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The Northwind API reference',
                'Every endpoint, parameter, and error code in one structured index — grouped by resource and runnable from the page.',
            ),
            renderData: [
                'summary' => 'A structured index of every Northwind resource: payments, customers, webhooks, and refunds, each with verbs, parameters, and copyable examples.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'API reference',
                        'heading' => 'Every endpoint, one searchable index',
                        'summary' => 'Browse the reference by resource. Each entry pairs the HTTP verb with its parameters, response schema, and a runnable example in the language you pick.',
                        'actions' => [
                            ['label' => 'Read the quickstart', 'url' => '#quickstart', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Northwind API reference index',
                    ],
                    $this->apiReferenceSection(
                        heading: 'Core resources',
                        summary: 'The endpoints most integrations touch on day one.',
                    ),
                    $this->contentListingSection(
                        heading: 'More of the surface',
                        summary: 'Less common endpoints, still documented to the same standard.',
                    ),
                    $this->sdkGridSection(
                        heading: 'Call it from an SDK instead',
                        summary: 'Every endpoint here maps to a typed method in the official clients.',
                    ),
                    $this->ctaSection(
                        heading: 'Try an endpoint live',
                        summary: 'Paste your test key into any example on this page and run it against the sandbox.',
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
            name: self::BRAND . ' Endpoint',
            title: 'Create a payment — POST /v1/payments — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'POST /v1/payments — create a payment',
                'The full reference for the payments endpoint: request body, idempotency, the response schema, and the errors you should handle.',
            ),
            renderData: [
                'summary' => 'A single endpoint page for POST /v1/payments that pairs the request example with idempotency rules, the response schema, and the errors to handle.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'POST /v1/payments',
                        'heading' => 'Create a payment',
                        'summary' => 'Charge a customer in any supported currency with a single POST. Pass an idempotency key so a retried request never double-charges, and read the captured payment straight back from the response.',
                        'actions' => [
                            ['label' => 'Run this request', 'url' => '#quickstart', 'style' => 'primary'],
                            ['label' => 'Back to the reference', 'url' => '#api-reference', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'The Northwind payments endpoint',
                    ],
                    $this->codeHeroSection(),
                    $this->endpointSpecSection(),
                    $this->sdkGridSection(
                        heading: 'Call it from your SDK',
                        summary: 'The same request, idiomatic in each official client, with idempotency and retries handled for you.',
                    ),
                    $this->proofSection(
                        heading: 'How teams use this endpoint',
                        summary: 'Where payments runs in production today.',
                    ),
                    $this->ctaSection(
                        heading: 'Send a test payment',
                        summary: 'Drop your sandbox key into the example and watch the captured payment come back. Flip to live keys when you ship.',
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
            name: self::BRAND . ' Developer Support',
            title: 'Developer support — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Talk to the engineers who build the API',
                'Open a support thread, join the developer community, or escalate a production incident — answered by the team that ships the platform.',
            ),
            renderData: [
                'summary' => 'Reach Northwind developer support directly — engineers answer integration questions, review your webhook setup, and triage incidents around the clock.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Developer support',
                        'heading' => 'Talk to the engineers behind the API',
                        'summary' => 'Email developers@northwind-api.example, open an issue in the SDK repos, or page on-call for a production incident. Integration questions get a same-day reply from someone who has read the code.',
                        'actions' => [
                            ['label' => 'Email developer support', 'url' => 'mailto:developers@northwind-api.example', 'style' => 'primary'],
                            ['label' => 'Read the quickstart', 'url' => '#quickstart', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Northwind developer support team',
                    ],
                    $this->featuresSection(
                        heading: 'How we support your integration',
                        summary: 'The channels engineers reach for, from a first question to a live incident.',
                    ),
                    $this->statusUptimeSection(
                        heading: 'Check status before you escalate',
                        summary: 'The public status page shows current health and the last 90 days at a glance.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to walk through it together?',
                        summary: 'Book a 30-minute integration review and an engineer will look at your webhook handlers and error handling with you.',
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
            title: 'No endpoints match that filter — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No endpoints match that filter',
                'A graceful empty state for an API reference search that returned nothing, with quick routes back into the docs.',
            ),
            renderData: [
                'summary' => 'No endpoints match that search yet — clear the filter to see the whole reference, or jump to the quickstart and SDKs.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'API reference',
                        'heading' => 'No endpoints match that filter — yet',
                        'summary' => 'Your search did not match any resource in the reference. Clear the filter to see the full surface, or start from the quickstart instead.',
                        'actions' => [
                            ['label' => 'View the full reference', 'url' => '#api-reference', 'style' => 'primary'],
                            ['label' => 'Read the quickstart', 'url' => '#quickstart', 'style' => 'secondary'],
                        ],
                    ],
                    $this->contentListingSection(
                        heading: 'Popular endpoints',
                        summary: 'The resources most integrations start with.',
                    ),
                    $this->sdkGridSection(
                        heading: 'Reach for an SDK instead',
                        summary: 'A typed client may save you the search entirely.',
                    ),
                    $this->ctaSection(
                        heading: 'Cannot find an endpoint?',
                        summary: 'Tell developer support what you are trying to build and we will point you at the right resource.',
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
                'A not-found page that routes developers back into the quickstart, reference, and status page.',
            ),
            renderData: [
                'summary' => 'That page returned a 404 — here is the way back into the docs, SDKs, and status page.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That route returned a 404',
                        'summary' => 'The link is broken or the page has moved. Head back to the quickstart and reference, or check the status page if something feels off.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Read the quickstart', 'url' => '#quickstart', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Looking for an endpoint?',
                        summary: 'Search the API reference, or email developer support and we will send you the right link.',
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
            name: self::BRAND . ' Get Started',
            title: 'Get your API key — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'From signup to first request in minutes',
                'A focused conversion page that moves a developer from interest to a live API call against the sandbox.',
            ),
            renderData: [
                'summary' => 'From signup to first live request in minutes. Grab a test key, run the quickstart, and switch to live keys when you ship.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Get started',
                        'heading' => 'From signup to first request in minutes',
                        'summary' => 'Create an account, copy your test key, and send a request against the sandbox. When the integration is ready, swap in live keys without changing a line of code.',
                        'actions' => [
                            ['label' => 'Read the quickstart', 'url' => '#quickstart', 'style' => 'primary'],
                            ['label' => 'Email developer support', 'url' => 'mailto:developers@northwind-api.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Get started with the Northwind API',
                    ],
                    $this->quickstartSection(
                        heading: 'Your first request, step by step',
                        summary: 'Four copy-paste steps from a fresh key to a live response.',
                    ),
                    $this->statusUptimeSection(
                        heading: 'Built on reliable infrastructure',
                        summary: 'The uptime record you are building on, published openly.',
                    ),
                    $this->ctaSection(
                        heading: 'Your test key is one click away',
                        summary: 'Sign up free, run the sandbox, and only reach for live keys when you are ready to ship.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function codeHeroSection(): array
    {
        return [
            'type' => 'code-hero',
            'heading' => 'One request, a captured payment back',
            'summary' => 'Authenticate with a bearer token, pass an idempotency key, and read the captured payment straight out of the response.',
            'items' => [
                [
                    'title' => 'POST /v1/payments',
                    'summary' => 'curl https://api.northwind-api.example/v1/payments -H "Authorization: Bearer sk_test_..." -H "Idempotency-Key: a1b2c3" -d amount=4200 -d currency=usd -d customer=cus_8Hq2',
                ],
                [
                    'title' => '200 OK — captured',
                    'summary' => 'The response returns the payment object with its id, status "succeeded", the amount in minor units, and the idempotency key echoed back for your records.',
                ],
                [
                    'title' => 'Idempotent by design',
                    'summary' => 'Send the same idempotency key twice and the second request returns the original payment instead of charging again. Retries are safe.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function quickstartSection(string $heading, string $summary): array
    {
        return [
            'type' => 'quickstart',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => '1. Create your API keys',
                    'summary' => 'Sign up and copy your test secret key from the dashboard. Test keys are sandboxed — nothing you do touches real money.',
                ],
                [
                    'title' => '2. Authenticate the request',
                    'summary' => 'Pass the key as a bearer token in the Authorization header. Every endpoint uses the same scheme, so you wire it once.',
                ],
                [
                    'title' => '3. Send your first call',
                    'summary' => 'POST to /v1/payments with an amount, currency, and customer. Add an Idempotency-Key header so retries never double-charge.',
                ],
                [
                    'title' => '4. Handle the webhook',
                    'summary' => 'Subscribe to payment.succeeded, verify the signature on the event, and update your order. You are now live end to end.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function apiReferenceSection(string $heading, string $summary): array
    {
        return [
            'type' => 'api-reference-teaser',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Payments — /v1/payments',
                    'summary' => 'Create, capture, and retrieve payments. Documents idempotency, partial captures, and the full set of decline codes.',
                ],
                [
                    'title' => 'Customers — /v1/customers',
                    'summary' => 'Store customers and their saved payment methods, then charge them by reference without re-collecting card details.',
                ],
                [
                    'title' => 'Refunds — /v1/refunds',
                    'summary' => 'Issue full or partial refunds against a payment and track them to settlement, with reason codes and metadata.',
                ],
                [
                    'title' => 'Webhooks — /v1/webhook_endpoints',
                    'summary' => 'Register endpoints, choose the events you care about, and verify every delivery with a signed header.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function endpointSpecSection(): array
    {
        return [
            'type' => 'api-reference-teaser',
            'heading' => 'Request and response, in full',
            'summary' => 'The contract for POST /v1/payments — parameters, the object you get back, and the errors worth handling.',
            'items' => [
                [
                    'title' => 'amount (required)',
                    'summary' => 'A positive integer in the currency\'s minor units — 4200 for $42.00. Validated against the currency\'s precision before the charge.',
                ],
                [
                    'title' => 'currency (required)',
                    'summary' => 'A three-letter ISO 4217 code such as usd, eur, or gbp. The full list of supported currencies is in the reference.',
                ],
                [
                    'title' => 'Idempotency-Key (header)',
                    'summary' => 'A unique string per logical request. Northwind stores the result for 24 hours so a retried call returns the same payment.',
                ],
                [
                    'title' => 'Errors to handle',
                    'summary' => '402 card_declined with a decline_code, 400 for validation, and 409 if an idempotency key is reused with a different body.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sdkGridSection(string $heading, string $summary): array
    {
        return [
            'type' => 'sdk-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Node.js',
                    'summary' => 'npm install northwind. A fully typed client with automatic retries, cursor pagination, and idempotency keys generated for you.',
                ],
                [
                    'title' => 'Python',
                    'summary' => 'pip install northwind. Synchronous and async clients, typed models, and structured exceptions for every API error class.',
                ],
                [
                    'title' => 'Go',
                    'summary' => 'go get northwind-api/northwind-go. Context-aware, zero-dependency, with strongly typed request and response structs.',
                ],
                [
                    'title' => 'PHP',
                    'summary' => 'composer require northwind/northwind-php. PSR-18 transport, typed resource objects, and built-in webhook signature verification.',
                ],
                [
                    'title' => 'Ruby',
                    'summary' => 'gem install northwind. Idiomatic resource methods, automatic pagination, and configurable retry and timeout policies.',
                ],
                [
                    'title' => 'Java',
                    'summary' => 'A Maven artifact with a builder-style client, typed responses, and connection pooling tuned for high-throughput services.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function statusUptimeSection(string $heading, string $summary): array
    {
        return [
            'type' => 'status-uptime',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => '99.99% uptime',
                    'summary' => 'Rolling 90-day availability for the core payments and identity APIs, measured from outside our network.',
                ],
                [
                    'title' => '74ms median latency',
                    'summary' => 'p50 response time for authenticated requests, with p99 held under 220ms across every region.',
                ],
                [
                    'title' => 'Signed webhooks',
                    'summary' => 'Every event ships with a signature and a timestamp, retried with exponential backoff until your endpoint acknowledges it.',
                ],
                [
                    'title' => 'Public status page',
                    'summary' => 'Live health, scheduled maintenance, and a full incident history — published openly and subscribable by RSS.',
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
                    'title' => 'Idempotent requests',
                    'summary' => 'Every write accepts an idempotency key, so a network retry returns the original result instead of duplicating it.',
                ],
                [
                    'title' => 'Versioned, never broken',
                    'summary' => 'The API is pinned by date. New behaviour ships behind a new version, so your integration keeps working untouched.',
                ],
                [
                    'title' => 'Typed error shapes',
                    'summary' => 'Errors return a stable type, code, and human message, so you branch on the code instead of parsing strings.',
                ],
                [
                    'title' => 'Sandbox that mirrors live',
                    'summary' => 'Test keys hit a sandbox with the same endpoints, validation, and webhooks — so nothing surprises you in production.',
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
            'items' => [
                [
                    'title' => 'Payment methods — /v1/payment_methods',
                    'summary' => 'Attach cards and bank accounts to a customer and reuse them for future charges without re-collecting details.',
                ],
                [
                    'title' => 'Payouts — /v1/payouts',
                    'summary' => 'Move settled balance to a connected bank account and track each payout through to arrival.',
                ],
                [
                    'title' => 'Events — /v1/events',
                    'summary' => 'Replay any webhook event from the last 30 days for debugging or backfilling a missed delivery.',
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
                [
                    'title' => '99.99% uptime',
                    'summary' => 'Rolling 90-day availability on the core API, measured externally and shown on the public status page.',
                ],
                [
                    'title' => '2.1B requests / month',
                    'summary' => 'Production traffic the platform handles across payments, identity, and webhooks.',
                ],
                [
                    'title' => '6 official SDKs',
                    'summary' => 'Typed clients versioned in lockstep with the API, so upgrades never drift from the docs.',
                ],
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
                ['label' => 'Read the quickstart', 'url' => '#quickstart', 'style' => 'primary'],
                ['label' => 'Browse the API reference', 'url' => '#api-reference', 'style' => 'secondary'],
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
                ['label' => 'Quickstart', 'url' => '#quickstart'],
                ['label' => 'API Reference', 'url' => '#api-reference'],
                ['label' => 'SDKs', 'url' => '#sdk-grid'],
                ['label' => 'Status', 'url' => '#status-uptime'],
                ['label' => 'Support', 'url' => '#contact'],
            ],
            'ctaLabel' => 'Read the quickstart',
            'ctaUrl' => '#quickstart',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A developer-first API platform for payments, identity, and webhooks — versioned, idempotent, and built to stay up.',
            'columns' => [
                [
                    'heading' => 'Developers',
                    'links' => [
                        ['label' => 'Quickstart', 'url' => '#quickstart'],
                        ['label' => 'API reference', 'url' => '#api-reference'],
                        ['label' => 'SDKs', 'url' => '#sdk-grid'],
                        ['label' => 'Webhooks', 'url' => '#api-reference'],
                    ],
                ],
                [
                    'heading' => 'Platform',
                    'links' => [
                        ['label' => 'Status page', 'url' => '#status-uptime'],
                        ['label' => 'Changelog', 'url' => '#api-reference'],
                        ['label' => 'Idempotency', 'url' => '#quickstart'],
                        ['label' => 'Versioning', 'url' => '#api-reference'],
                    ],
                ],
                [
                    'heading' => 'Support',
                    'links' => [
                        ['label' => 'Developer support', 'url' => '#contact'],
                        ['label' => 'Get an API key', 'url' => '#cta'],
                        ['label' => 'developers@northwind-api.example', 'url' => 'mailto:developers@northwind-api.example'],
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

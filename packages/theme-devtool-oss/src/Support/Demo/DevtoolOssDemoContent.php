<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DevtoolOss\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Devtool OSS theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (install-hero / github-proof /
 * self-host-vs-cloud / sdk-grid / changelog / contributors) alongside the standard
 * hero/features/proof/cta — giving every surface a full, individual open-source
 * developer-tool site rather than a five-section skeleton.
 */
final class DevtoolOssDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Forgewright';

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
            title: self::BRAND . ' — Open-Source Developer Tooling',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A developer tool teams can build on',
                'Forgewright is an open-source toolkit you can self-host in minutes or run on our cloud, with SDKs, a public changelog, and a community behind every release.',
            ),
            renderData: [
                'summary' => 'Forgewright is open-source developer tooling. Install in one command, ship with first-party SDKs, and choose self-host or managed cloud without changing a line of code.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Open-source developer tooling',
                        'heading' => 'A developer tool teams can build on',
                        'summary' => 'Install flows, GitHub proof, SDKs, a public changelog, and a self-host versus cloud path — everything a developer needs to adopt the tool with confidence.',
                        'actions' => [
                            ['label' => 'Get started', 'url' => '#install', 'style' => 'primary'],
                            ['label' => 'View on GitHub', 'url' => '#github', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Forgewright developer tool',
                    ],
                    $this->installHeroSection(
                        heading: 'Install in one confident command',
                        summary: 'Copy, paste, run. Forgewright is a single binary with no runtime to babysit, so the first install feels like part of the product.',
                    ),
                    $this->githubProofSection(
                        heading: 'Trusted in the open',
                        summary: 'The signals developers check before they adopt — stars, releases, and an issue tracker that answers back.',
                    ),
                    $this->featuresSection(
                        heading: 'Built for the way developers actually work',
                        summary: 'From local development to production, Forgewright fits the toolchain your team already runs.',
                    ),
                    $this->selfHostVsCloudSection(
                        heading: 'Self-host or cloud, same tool',
                        summary: 'Run it on your own infrastructure for full control, or let our managed cloud carry the operations. Switch whenever it suits you.',
                    ),
                    $this->sdkGridSection(
                        heading: 'First-party SDKs for every stack',
                        summary: 'Typed, versioned client libraries that track the core release, so the language you ship in is never an afterthought.',
                    ),
                    $this->changelogSection(
                        heading: 'A changelog you can rely on',
                        summary: 'Every release documented in the open, with semantic versions and migration notes you can act on.',
                    ),
                    $this->contributorsSection(
                        heading: 'Maintained by a real community',
                        summary: 'Core maintainers and outside contributors who review in days, not weeks.',
                    ),
                    $this->proofSection(
                        heading: 'Why teams standardise on Forgewright',
                        summary: 'The numbers behind the project.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to run your first install?',
                        summary: 'Drop the install command into your terminal, or spin up a managed workspace in the cloud. Either way you are running in minutes.',
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
            name: self::BRAND . ' SDKs',
            title: 'SDKs & libraries — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A catalog of SDKs built to be scanned',
                'Browse every first-party client library — typed, versioned, and built to be scanned, with install snippets for each language.',
            ),
            renderData: [
                'summary' => 'First-party SDKs across every supported language. Filter by stack, open the docs, and copy the install snippet in one click.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'SDKs & libraries',
                        'heading' => 'A catalog of SDKs built to be scanned',
                        'summary' => 'Structured SDK cards keep the project legible. Filter by language, check the version, and grab the install command without leaving the page.',
                        'actions' => [
                            ['label' => 'Install the core', 'url' => '#install', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Forgewright SDK catalog',
                    ],
                    $this->sdkGridSection(
                        heading: 'Featured client libraries',
                        summary: 'The SDKs teams reach for first, each tracking the latest core release.',
                    ),
                    $this->contentListingSection(
                        heading: 'More from the library archive',
                        summary: 'Community SDKs, framework adapters, and integration plugins.',
                    ),
                    $this->ctaSection(
                        heading: 'Need a binding you do not see here?',
                        summary: 'Tell us the language you ship in and we will point you to a community SDK or help you scaffold one against the public API.',
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
            name: self::BRAND . ' Project',
            title: 'Forgewright Core — project — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A project page that reads with confidence',
                'A single project view pairs GitHub proof, the changelog, and the feature set so developers can judge the tool and adopt it in minutes.',
            ),
            renderData: [
                'summary' => 'One project view brings the GitHub proof, the public changelog, and the feature set together so developers can adopt the tool with confidence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Project',
                        'heading' => 'A project page that reads with confidence',
                        'summary' => 'Forgewright Core — the single binary at the heart of the toolkit. Release health, recent changes, and the feature set in one scannable view.',
                        'actions' => [
                            ['label' => 'Back to all SDKs', 'url' => '#sdks', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Forgewright Core project',
                    ],
                    $this->githubProofSection(
                        heading: 'Proof a maintainer can stand behind',
                        summary: 'Stars, the latest tagged release, open issues, and the median time to a first response.',
                    ),
                    $this->changelogSection(
                        heading: 'What shipped recently',
                        summary: 'The last few releases with semantic versions and the migration notes that matter.',
                    ),
                    $this->featuresSection(
                        heading: 'What Forgewright Core gives you',
                        summary: 'The capabilities the binary ships with out of the box, before a single plugin.',
                    ),
                    $this->ctaSection(
                        heading: 'Want to run Forgewright Core?',
                        summary: 'Install it locally with one command, or open a managed workspace. Most teams are running their first job inside ten minutes.',
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
            title: 'Reach the maintainers — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Reach the maintainers through one clear path',
                'Open an issue, join the community chat, or email the core team. Every support path feels like part of the developer experience.',
            ),
            renderData: [
                'summary' => 'Reach the maintainers through one clear path. Open an issue, join the chat, or email the core team — support is part of the product, not a hand-off.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Contact',
                        'heading' => 'Reach the maintainers through one clear path',
                        'summary' => 'Email maintainers@forgewright.example, open a GitHub issue, or join the community chat. Public questions get public answers, fast.',
                        'actions' => [
                            ['label' => 'Email the maintainers', 'url' => 'mailto:maintainers@forgewright.example', 'style' => 'primary'],
                            ['label' => 'Open an issue', 'url' => '#github', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Forgewright maintainers',
                    ],
                    $this->featuresSection(
                        heading: 'How we support the project',
                        summary: 'The support paths that come with the tool, whether you self-host or run on cloud.',
                    ),
                    $this->contributorsSection(
                        heading: 'The people who answer',
                        summary: 'Core maintainers who triage issues and review pull requests every working day.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to start with the docs?',
                        summary: 'The quickstart walks you from install to first job. Hit a wall and the maintainers reply on the issue tracker within a working day.',
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
            title: 'Nothing published yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing published here yet',
                'A graceful empty state for a filtered SDK library with no matching libraries.',
            ),
            renderData: [
                'summary' => 'No libraries match that filter yet — but Forgewright can still point you to the next useful step.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'SDK library',
                        'heading' => 'Nothing published here yet',
                        'summary' => 'No libraries match that filter. Clear it to see every SDK, or install the core and start building against the public API today.',
                        'actions' => [
                            ['label' => 'View all SDKs', 'url' => '#sdks', 'style' => 'primary'],
                            ['label' => 'Install the core', 'url' => '#install', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'No libraries to show here',
                        'summary' => 'When SDKs in this category are published they will appear here, newest release first.',
                        'items' => [],
                    ],
                    $this->featuresSection(
                        heading: 'While you are here',
                        summary: 'The capabilities the core binary ships with, ready the moment you install.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific binding?',
                        summary: 'Tell us the language you ship in and we will point you to a community SDK or help you scaffold one.',
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
                'That page could not be found',
                'A not-found page that routes developers back into the install flow and the docs.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the docs.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page could not be found',
                        'summary' => 'The link is broken or the page has moved. Head back to the install flow, or jump into the SDK catalog.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Install the core', 'url' => '#install', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us what you needed and we will point you to the right page in the docs.',
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
            title: 'Run your first install — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn intent into a first install',
                'A focused conversion page that turns "should we adopt this?" into a running install — self-hosted or on cloud.',
            ),
            renderData: [
                'summary' => 'Turn intent into a first install. Run the core on your own infrastructure or open a managed cloud workspace and ship today.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Get started',
                        'heading' => 'Turn intent into a first install',
                        'summary' => 'Whether you self-host the open-source core or run on managed cloud, you get the same tool and the same SDKs. Set up takes minutes.',
                        'actions' => [
                            ['label' => 'Install the core', 'url' => '#install', 'style' => 'primary'],
                            ['label' => 'Start on cloud', 'url' => '#cloud', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Forgewright workspace',
                    ],
                    $this->installHeroSection(
                        heading: 'One command to your first run',
                        summary: 'Copy the install command, run it, and Forgewright is ready. No runtime, no config marathon.',
                    ),
                    $this->proofSection(
                        heading: 'Why teams choose Forgewright',
                        summary: 'The numbers behind the project.',
                    ),
                    $this->ctaSection(
                        heading: 'One install away',
                        summary: 'Run the open-source core or spin up cloud. Either path has you shipping in minutes, with the maintainers a single issue away.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function installHeroSection(string $heading, string $summary): array
    {
        return [
            'type' => 'install-hero',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'curl -fsSL get.forgewright.dev | sh', 'summary' => 'One script installs the single binary on Linux and macOS — no package manager required.'],
                ['title' => 'brew install forgewright', 'summary' => 'Homebrew users get the same release with automatic upgrades on every brew bump.'],
                ['title' => 'docker run forgewright/core', 'summary' => 'Run the official image in CI or production without touching the host system.'],
                ['title' => 'forgewright init', 'summary' => 'Scaffold a project, point it at your config, and run your first job in under a minute.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function githubProofSection(string $heading, string $summary): array
    {
        return [
            'type' => 'github-proof',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => '14.2k stars', 'summary' => 'A growing repository developers watch, fork, and depend on in production.'],
                ['title' => 'v3.8.0 latest release', 'summary' => 'Tagged, signed, and shipped with full release notes on a predictable cadence.'],
                ['title' => 'Apache 2.0 licensed', 'summary' => 'A permissive licence you can adopt in commercial work without legal friction.'],
                ['title' => '< 24h first response', 'summary' => 'Median time to a maintainer reply on a new issue, measured across the last quarter.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function selfHostVsCloudSection(string $heading, string $summary): array
    {
        return [
            'type' => 'self-host-vs-cloud',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Self-host', 'summary' => 'Run the open-source core on your own infrastructure with full control over data, networking, and upgrades.'],
                ['title' => 'Managed cloud', 'summary' => 'Let us handle scaling, backups, and patching while you keep the exact same API and SDKs.'],
                ['title' => 'No lock-in', 'summary' => 'Your config and data are portable, so you can move between self-host and cloud whenever it suits you.'],
                ['title' => 'One toolchain', 'summary' => 'The CLI, SDKs, and changelog are identical across both paths — nothing to relearn when you switch.'],
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
                ['title' => 'TypeScript SDK', 'summary' => 'Fully typed client with first-class ESM support, published to npm on every core release.'],
                ['title' => 'Go SDK', 'summary' => 'Idiomatic, context-aware client built for services and CLIs, with zero external dependencies.'],
                ['title' => 'Python SDK', 'summary' => 'Sync and async clients with type hints, packaged on PyPI and tested against each release.'],
                ['title' => 'Rust SDK', 'summary' => 'A crate with async support and strict typing for the most performance-sensitive integrations.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function changelogSection(string $heading, string $summary): array
    {
        return [
            'type' => 'changelog',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'v3.8.0 — Streaming jobs', 'summary' => 'Long-running jobs now stream progress over the API, with backpressure handled for you.'],
                ['title' => 'v3.7.0 — Plugin sandbox', 'summary' => 'Third-party plugins run in an isolated sandbox with explicit capability grants.'],
                ['title' => 'v3.6.2 — Faster cold start', 'summary' => 'Binary start-up time cut by 40% so CI runs and serverless invocations land sooner.'],
                ['title' => 'v3.6.0 — Config as code', 'summary' => 'Declarative config with schema validation and a documented migration path from v2.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contributorsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'contributors',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Maya Okonkwo', 'summary' => 'Project lead and core maintainer. Owns the release process and the public roadmap.'],
                ['title' => 'Daniel Reyes', 'summary' => 'Runtime engineer. Keeps the binary small, fast, and dependency-free across platforms.'],
                ['title' => 'Hannah Lindqvist', 'summary' => 'SDK maintainer. Makes sure every client library tracks the core release on day one.'],
                ['title' => '320+ contributors', 'summary' => 'The wider community shipping fixes, plugins, and docs through reviewed pull requests.'],
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
                ['title' => 'Single binary', 'summary' => 'No runtime, no service mesh — one self-contained binary you can drop into any environment.'],
                ['title' => 'Config as code', 'summary' => 'Declarative, version-controlled config with schema validation and clear migration notes.'],
                ['title' => 'Plugin system', 'summary' => 'Extend the core with sandboxed plugins that declare exactly the capabilities they need.'],
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
                ['metric' => '14.2k', 'name' => 'GitHub stars', 'quote' => 'A repository developers watch, fork, and run in production.'],
                ['metric' => '4', 'name' => 'First-party SDKs', 'quote' => 'TypeScript, Go, Python, and Rust, each tracking the core release.'],
                ['metric' => '< 24h', 'name' => 'To a maintainer reply', 'quote' => 'Median first response on a new issue across the last quarter.'],
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
                ['title' => 'Laravel adapter', 'summary' => 'A community package that wires Forgewright into Laravel queues and config.'],
                ['title' => 'Terraform provider', 'summary' => 'Manage managed-cloud workspaces and self-host config as infrastructure as code.'],
                ['title' => 'VS Code extension', 'summary' => 'Inline job status and config validation without leaving the editor.'],
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
                ['label' => 'Install the core', 'url' => '#install', 'style' => 'primary'],
                ['label' => 'View on GitHub', 'url' => '#github', 'style' => 'secondary'],
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
                ['label' => 'Install', 'url' => '#install'],
                ['label' => 'SDKs', 'url' => '#sdks'],
                ['label' => 'Changelog', 'url' => '#changelog'],
                ['label' => 'Community', 'url' => '#community'],
                ['label' => 'Cloud', 'url' => '#cloud'],
            ],
            'ctaLabel' => 'Get started',
            'ctaUrl' => '#install',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'Open-source developer tooling. Self-host the core or run it on managed cloud.',
            'columns' => [
                [
                    'heading' => 'Product',
                    'links' => [
                        ['label' => 'Install', 'url' => '#install'],
                        ['label' => 'SDKs', 'url' => '#sdks'],
                        ['label' => 'Changelog', 'url' => '#changelog'],
                        ['label' => 'Cloud vs self-host', 'url' => '#cloud'],
                    ],
                ],
                [
                    'heading' => 'Community',
                    'links' => [
                        ['label' => 'GitHub', 'url' => '#github'],
                        ['label' => 'Contributors', 'url' => '#community'],
                        ['label' => 'Discussions', 'url' => '#community'],
                        ['label' => 'Roadmap', 'url' => '#community'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Open an issue', 'url' => '#github'],
                        ['label' => 'maintainers@forgewright.example', 'url' => 'mailto:maintainers@forgewright.example'],
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

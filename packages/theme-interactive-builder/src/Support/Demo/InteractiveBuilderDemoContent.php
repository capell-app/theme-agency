<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InteractiveBuilder\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Interactive Builder theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (canvas-workspace /
 * builder-capabilities / templates / collaboration-comments / community-showcase /
 * publishing-controls) alongside the standard hero/proof/cta — giving every
 * surface a full no-code builder product site rather than the shared skeleton.
 */
final class InteractiveBuilderDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Canvasflow';

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
            title: self::BRAND . ' — The Visual Builder for Product Teams',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Build, collaborate, and publish on one interactive canvas',
                'Canvasflow is a visual builder where product teams design, comment, and ship — without writing a line of code.',
            ),
            renderData: [
                'summary' => 'Canvasflow is the visual builder where teams design on a live canvas, collaborate in comments, and publish responsive work without code.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Interactive Builder',
                        'heading' => 'Build, collaborate, and publish on one interactive canvas',
                        'summary' => 'A dark, high-contrast workspace for visual builders. Drag, drop, comment, and ship responsive work — no code, no handoff, no waiting.',
                        'actions' => [
                            ['label' => 'Open the canvas', 'url' => '#canvas-workspace', 'style' => 'primary'],
                            ['label' => 'Browse templates', 'url' => '#templates', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Canvasflow builder canvas',
                    ],
                    $this->canvasWorkspaceSection(
                        heading: 'A canvas built to be shown, not explained',
                        summary: 'Layers, assets, and breakpoints in one high-contrast surface. Everything you place is real, responsive, and ready to ship.',
                    ),
                    $this->builderCapabilitiesSection(
                        heading: 'Everything you need to go from idea to live',
                        summary: 'The building blocks of a real no-code product, in one workspace.',
                    ),
                    $this->templatesSection(
                        heading: 'Start from a template, not a blank canvas',
                        summary: 'Production-ready starting points for landing pages, dashboards, and full sites.',
                    ),
                    $this->collaborationCommentsSection(
                        heading: 'Feedback that lives on the canvas',
                        summary: 'Pin comments to any element, resolve threads in context, and keep design and build in one conversation.',
                    ),
                    $this->communityShowcaseSection(
                        heading: 'Built with Canvasflow',
                        summary: 'A few of the products, sites, and tools the community has shipped on the canvas.',
                    ),
                    $this->publishingControlsSection(
                        heading: 'Publishing controls you can trust',
                        summary: 'Preview every breakpoint, stage changes, and publish to production in one click.',
                    ),
                    $this->proofSection(
                        heading: 'Trusted by teams who ship',
                        summary: 'What teams get when they move to the canvas.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to build on the canvas?',
                        summary: 'Start free, invite your team, and publish your first project today.',
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
            name: self::BRAND . ' Templates',
            title: 'Template Library — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A template library built to be scanned',
                'Production-ready templates for landing pages, dashboards, and full sites — clone one and start building.',
            ),
            renderData: [
                'summary' => 'Production-ready templates for landing pages, dashboards, marketplaces, and full sites. Clone one and start building on the canvas.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Template library',
                        'heading' => 'A template library built to be scanned',
                        'summary' => 'Every template opens directly on the canvas — fully responsive, fully editable, ready to make your own.',
                        'actions' => [
                            ['label' => 'Open the canvas', 'url' => '#canvas-workspace', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Canvasflow template gallery',
                    ],
                    $this->templatesSection(
                        heading: 'Featured templates',
                        summary: 'The starting points teams clone most.',
                    ),
                    $this->communityShowcaseSection(
                        heading: 'Made by the community',
                        summary: 'Templates and builds shared by Canvasflow makers.',
                    ),
                    $this->contentListingSection(
                        heading: 'More from the gallery',
                        summary: 'Smaller starters, components, and experiments.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Found a template that fits?',
                        summary: 'Clone it to your workspace and start editing on the canvas in seconds.',
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
            name: self::BRAND . ' Feature',
            title: 'The Canvas — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'The canvas, in detail',
                'How the Canvasflow workspace turns layers, assets, and breakpoints into shippable work.',
            ),
            renderData: [
                'summary' => 'A close look at the canvas — layers, assets, breakpoints, and live preview — and how it turns a blank workspace into shippable product.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Feature',
                        'heading' => 'The canvas, in detail',
                        'summary' => 'A high-contrast workspace where every layer, asset, and breakpoint is real. What you build is what you ship.',
                        'actions' => [
                            ['label' => 'Browse templates', 'url' => '#templates', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Canvasflow canvas workspace',
                    ],
                    $this->canvasWorkspaceSection(
                        heading: 'One workspace, every layer',
                        summary: 'Floating layer panels, an asset library, and breakpoint previews share a single high-contrast surface.',
                    ),
                    $this->publishingControlsSection(
                        heading: 'From draft to live',
                        summary: 'Stage, preview, and publish without leaving the canvas.',
                    ),
                    $this->collaborationCommentsSection(
                        heading: 'Built for teams, not handoffs',
                        summary: 'Comment threads stay pinned to the work, so feedback never leaves the canvas.',
                    ),
                    $this->ctaSection(
                        heading: 'Want the canvas for your team?',
                        summary: 'Start a workspace and invite the people who build with you.',
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
            title: 'Talk to us — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Start a conversation through one confident path',
                'Tell us what you are building and we will help you map it onto the canvas.',
            ),
            renderData: [
                'summary' => 'Tell us what you are building. Our team will help you map it onto the canvas and get your workspace set up.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Contact',
                        'heading' => 'Start a conversation through one confident path',
                        'summary' => 'Email hello@canvasflow.example or use the form below. We reply to every team within one working day.',
                        'actions' => [
                            ['label' => 'Email the team', 'url' => 'mailto:hello@canvasflow.example', 'style' => 'primary'],
                            ['label' => 'Open the canvas', 'url' => '#canvas-workspace', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Canvasflow team',
                    ],
                    $this->newsletterSection(
                        heading: 'Get product updates',
                        summary: 'New templates, canvas features, and showcase builds — once a month, no noise.',
                    ),
                    $this->builderCapabilitiesSection(
                        heading: 'How teams use the canvas',
                        summary: 'Pick the shape that fits how you build.',
                    ),
                    $this->proofSection(
                        heading: 'What to expect',
                        summary: 'How onboarding works once you start.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to see it live?',
                        summary: 'Book a 30-minute walkthrough and we will build something on the canvas with you.',
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
            title: 'No results — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing on the canvas yet',
                'A graceful empty state for a filtered template gallery with no matching results.',
            ),
            renderData: [
                'summary' => 'No templates match that filter yet — but there is still plenty to build with.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Template gallery',
                        'heading' => 'No templates match that filter — yet',
                        'summary' => 'Clear the filter to see the full gallery, or start from a blank canvas and build it yourself.',
                        'actions' => [
                            ['label' => 'Browse templates', 'url' => '#templates', 'style' => 'primary'],
                            ['label' => 'Open the canvas', 'url' => '#canvas-workspace', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'templates',
                        'heading' => 'Nothing to show here',
                        'summary' => 'When templates land in this category they will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->builderCapabilitiesSection(
                        heading: 'While you are here',
                        summary: 'The building blocks the canvas is best known for.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us what you need and we will point you to the right starting point.',
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
                'A not-found page that routes visitors back into the canvas and template paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back to the canvas.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This layer never made it to the canvas',
                        'summary' => 'The link is broken or the page has moved. Head back to the templates, or open a fresh canvas.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Browse templates', 'url' => '#templates', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and we will route you to the right place.',
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
            title: 'Start building — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Ready to build on the canvas?',
                'A focused conversion page inviting teams to start a workspace.',
            ),
            renderData: [
                'summary' => 'Ready to build on the canvas? Start a free workspace and ship your first project today.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Get started',
                        'heading' => 'Ready to build on the canvas?',
                        'summary' => 'Whether it is a single landing page or a full product, the same workspace takes you from blank canvas to live.',
                        'actions' => [
                            ['label' => 'Start free', 'url' => '#canvas-workspace', 'style' => 'primary'],
                            ['label' => 'Email the team', 'url' => 'mailto:hello@canvasflow.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Canvasflow workspace',
                    ],
                    $this->proofSection(
                        heading: 'Why teams choose Canvasflow',
                        summary: 'The numbers behind the canvas.',
                    ),
                    $this->publishingControlsSection(
                        heading: 'Ship the moment you are ready',
                        summary: 'Preview, stage, and publish without leaving the workspace.',
                    ),
                    $this->ctaSection(
                        heading: 'One canvas away',
                        summary: 'Create a workspace, invite your team, and publish your first project today.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function canvasWorkspaceSection(string $heading, string $summary): array
    {
        return [
            'type' => 'canvas-workspace',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Floating layer panels', 'summary' => 'Reorder, group, and lock layers on a high-contrast canvas that mirrors production exactly.'],
                ['title' => 'A shared asset library', 'summary' => 'Drop in images, components, and tokens once, then reuse them across every project.'],
                ['title' => 'Breakpoint previews', 'summary' => 'Switch between mobile, tablet, and desktop without leaving the canvas — what you see ships.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function builderCapabilitiesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'builder-capabilities',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Drag-and-drop canvas', 'summary' => 'A no-code surface where every element is responsive and production-ready by default.'],
                ['title' => 'Reusable components', 'summary' => 'Build once, sync everywhere. Update a component and every instance follows.'],
                ['title' => 'AI helper panel', 'summary' => 'Generate layouts, rewrite copy, and clean up structure with an assistant built into the canvas.'],
                ['title' => 'One-click publishing', 'summary' => 'Push responsive work to production from the workspace — no export, no handoff.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function templatesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'templates',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Launch — SaaS landing', 'summary' => 'A high-contrast marketing page with hero, features, and pricing wired to the canvas.', 'meta' => 'Landing page', 'care_note' => 'Fully responsive'],
                ['title' => 'Console — product dashboard', 'summary' => 'A data-dense dashboard shell with panels, tables, and a sidebar ready to populate.', 'meta' => 'Dashboard', 'care_note' => 'Dark + light'],
                ['title' => 'Gallery — template marketplace', 'summary' => 'A community showcase grid with filters, cards, and detail views built in.', 'meta' => 'Marketplace', 'care_note' => 'Filterable'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function collaborationCommentsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'collaboration-comments',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'See how comments work',
            'url' => '#publishing-controls',
            'items' => [
                ['title' => 'Pinned comment threads', 'summary' => 'Drop feedback on any element and keep the conversation attached to the work.'],
                ['title' => 'Resolve in context', 'summary' => 'Close threads as you ship, with a clear trail of what changed and why.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function communityShowcaseSection(string $heading, string $summary): array
    {
        return [
            'type' => 'community-showcase',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['meta' => 'Marketplace', 'title' => 'Driftboard', 'summary' => 'A two-person team shipped a full template marketplace on the canvas in a weekend.'],
                ['meta' => 'Product', 'title' => 'Northlight Console', 'summary' => 'An analytics dashboard built and published without a single line of front-end code.'],
                ['meta' => 'Landing', 'title' => 'Studio Verda', 'summary' => 'An architecture studio launched its portfolio site straight from a Canvasflow template.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function publishingControlsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'publishing-controls',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Staged previews', 'summary' => 'Share a private preview link before anything reaches production.'],
                ['title' => 'Breakpoint checks', 'summary' => 'Confirm mobile, tablet, and desktop render exactly as designed before you ship.'],
                ['title' => 'One-click publish', 'summary' => 'Promote staged work to live in a single action, with instant rollback.'],
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
                ['value' => '0', 'label' => 'Lines of code to publish your first project'],
                ['value' => '3x', 'label' => 'Faster from idea to live than a code-and-handoff workflow'],
                ['value' => '12k+', 'label' => 'Projects shipped on the canvas by builders worldwide'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsletterSection(string $heading, string $summary): array
    {
        return [
            'type' => 'newsletter',
            'heading' => $heading,
            'summary' => $summary,
            'action' => '#',
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['title' => 'Pricing table starter', 'category' => 'Component', 'summary' => 'A flexible pricing block with toggles and tiers, ready to drop onto any page.'],
            ['title' => 'Auth flow kit', 'category' => 'Flow', 'summary' => 'Sign-in, sign-up, and reset screens wired together as a reusable flow.'],
            ['title' => 'Empty-state pack', 'category' => 'Component', 'summary' => 'A set of polished empty states for dashboards, lists, and search results.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#starter-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'gallery',
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
            'label' => 'Start free',
            'url' => '#canvas-workspace',
            'actions' => [
                ['label' => 'Start free', 'url' => '#canvas-workspace', 'style' => 'primary'],
                ['label' => 'Browse templates', 'url' => '#templates', 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'items' => [
                ['label' => 'Canvas', 'url' => '#canvas-workspace'],
                ['label' => 'Templates', 'url' => '#templates'],
                ['label' => 'Community', 'url' => '#community-showcase'],
                ['label' => 'Publishing', 'url' => '#publishing-controls'],
                ['label' => 'Pricing', 'url' => '#cta'],
            ],
            'ctaLabel' => 'Start free',
            'ctaUrl' => '#canvas-workspace',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'The visual builder for product teams. Design, collaborate, and publish on one canvas.',
            'items' => [
                [
                    'title' => 'Product',
                    'heading' => 'Product',
                    'links' => [
                        ['label' => 'Canvas', 'url' => '#canvas-workspace'],
                        ['label' => 'Templates', 'url' => '#templates'],
                        ['label' => 'Publishing', 'url' => '#publishing-controls'],
                        ['label' => 'AI helpers', 'url' => '#builder-capabilities'],
                    ],
                ],
                [
                    'title' => 'Community',
                    'heading' => 'Community',
                    'links' => [
                        ['label' => 'Showcase', 'url' => '#community-showcase'],
                        ['label' => 'Templates', 'url' => '#templates'],
                        ['label' => 'Comments', 'url' => '#collaboration-comments'],
                    ],
                ],
                [
                    'title' => 'Connect',
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Contact', 'url' => '#contact'],
                        ['label' => 'hello@canvasflow.example', 'url' => 'mailto:hello@canvasflow.example'],
                    ],
                ],
            ],
            'columns' => [
                [
                    'heading' => 'Product',
                    'links' => [
                        ['label' => 'Canvas', 'url' => '#canvas-workspace'],
                        ['label' => 'Templates', 'url' => '#templates'],
                        ['label' => 'Publishing', 'url' => '#publishing-controls'],
                        ['label' => 'AI helpers', 'url' => '#builder-capabilities'],
                    ],
                ],
                [
                    'heading' => 'Community',
                    'links' => [
                        ['label' => 'Showcase', 'url' => '#community-showcase'],
                        ['label' => 'Templates', 'url' => '#templates'],
                        ['label' => 'Comments', 'url' => '#collaboration-comments'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Contact', 'url' => '#contact'],
                        ['label' => 'hello@canvasflow.example', 'url' => 'mailto:hello@canvasflow.example'],
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

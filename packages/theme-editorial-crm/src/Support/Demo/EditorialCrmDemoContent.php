<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\EditorialCrm\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Editorial CRM theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (product-dashboard /
 * data-model / workflow-automation / collaboration / integrations-reporting /
 * customer-stories / newsletter) alongside the standard hero/proof/cta — giving
 * every surface a full, individual revenue-CRM product site rather than the
 * shared five-section skeleton.
 */
final class EditorialCrmDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Cadence CRM';

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
            title: self::BRAND . ' — The CRM revenue teams stand behind',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'The CRM revenue teams can stand behind',
                'Cadence CRM gives revenue teams a single workspace for relationship records, workflow automation, reporting, and the customer stories that close deals.',
            ),
            renderData: [
                'summary' => 'Cadence CRM is the product workspace for revenue teams. Dashboards, relationship records, workflow automation, collaboration, integrations, and reporting in one place.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Revenue CRM',
                        'heading' => 'The CRM revenue teams can stand behind',
                        'summary' => 'A refined product workspace for dashboards, relationship records, workflow automation, collaboration, integrations, and reporting — built so every rep works from the same source of truth.',
                        'actions' => [
                            ['label' => 'Request a demo', 'url' => '#contact', 'style' => 'primary'],
                            ['label' => 'Explore the product', 'url' => '#product', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Cadence CRM product dashboard',
                    ],
                    $this->productDashboardSection(
                        heading: 'A dashboard your whole revenue team can read',
                        summary: 'Pipeline, activity, and forecast in one product surface — no spreadsheet exports, no stale numbers.',
                    ),
                    $this->dataModelSection(
                        heading: 'A relationship model that fits how you sell',
                        summary: 'Companies, contacts, deals, and activities connected the way your team already thinks about accounts.',
                    ),
                    $this->workflowAutomationSection(
                        heading: 'Automations that move deals forward on their own',
                        summary: 'Trigger follow-ups, route leads, and update records without a rep ever leaving the deal.',
                    ),
                    $this->collaborationSection(
                        heading: 'Sell as a team, not a stack of inboxes',
                        summary: 'Shared notes, @mentions, and deal rooms keep sales, success, and marketing on the same page.',
                    ),
                    $this->integrationsReportingSection(
                        heading: 'Plugs into the stack you already run',
                        summary: 'Two-way sync with email, calendar, support, and finance — then reporting that ties it all together.',
                    ),
                    $this->customerStoriesSection(
                        heading: 'Revenue teams who switched to Cadence',
                        summary: 'How operators replaced three tools with one workspace and grew win rates.',
                    ),
                    $this->newsletterSection(
                        heading: 'Get the Cadence revenue brief',
                        summary: 'A short monthly note on pipeline operations, automation patterns, and reporting we see working.',
                    ),
                    $this->ctaSection(
                        heading: 'See Cadence on your own pipeline',
                        summary: 'Book a guided demo and we will map Cadence to your sales process before you commit to anything.',
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
            name: self::BRAND . ' Product Updates',
            title: 'Product updates — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Product updates and release notes',
                'An archive of what shipped across dashboards, automation, integrations, and reporting in Cadence CRM.',
            ),
            renderData: [
                'summary' => 'An archive of product updates across dashboards, automation, integrations, and reporting — newest first.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Product updates',
                        'heading' => 'Everything we shipped, in one place',
                        'summary' => 'Structured release notes across automation, reporting, and integrations — scan the archive or filter by the part of Cadence you live in.',
                        'actions' => [
                            ['label' => 'Request a demo', 'url' => '#contact', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Cadence CRM product updates',
                    ],
                    $this->contentListingSection(
                        heading: 'An archive of product updates built to be scanned',
                        summary: 'Structured update cards keep releases, integrations, and reporting changes legible without losing the detail.',
                    ),
                    $this->customerStoriesSection(
                        heading: 'What teams built with these releases',
                        summary: 'Operators putting the latest automation and reporting work to use.',
                    ),
                    $this->ctaSection(
                        heading: 'Want a walkthrough of the latest release?',
                        summary: 'Book a demo and we will show the newest automation and reporting features on a pipeline like yours.',
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
            name: self::BRAND . ' Customer Story',
            title: 'Northwind — Customer Story — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'How Northwind unified three tools into Cadence',
                'A field-services team replaced a spreadsheet, a help desk, and a legacy CRM with one Cadence workspace.',
            ),
            renderData: [
                'summary' => 'How Northwind replaced a spreadsheet, a help desk, and a legacy CRM with one Cadence workspace — and lifted win rate.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Customer story',
                        'heading' => 'How Northwind unified three tools into Cadence',
                        'summary' => 'A 40-person field-services team had pipeline in spreadsheets and conversations scattered across inboxes. One workspace later, every rep works from the same record.',
                        'actions' => [
                            ['label' => 'Read more stories', 'url' => '#customer-stories', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Northwind revenue team using Cadence',
                    ],
                    $this->productDashboardSection(
                        heading: 'The dashboard Northwind runs every standup on',
                        summary: 'Pipeline and activity that used to live in a spreadsheet now update in real time for the whole team.',
                    ),
                    $this->workflowAutomationSection(
                        heading: 'The automations that cleared their busywork',
                        summary: 'Routing, follow-up, and record hygiene now run on their own so reps stay on live deals.',
                    ),
                    $this->customerStoriesSection(
                        heading: 'More revenue teams on Cadence',
                        summary: 'Other operators who consolidated their stack into one workspace.',
                    ),
                    $this->ctaSection(
                        heading: 'Want results like Northwind?',
                        summary: 'Book a demo and we will map Cadence to your sales process before you move a single record.',
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
            title: 'Request a demo — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Request a demo',
                'Tell us about your revenue team and we will tailor a Cadence CRM walkthrough to your sales process.',
            ),
            renderData: [
                'summary' => 'Tell us about your revenue team and pipeline — we tailor every Cadence demo to your sales process and stack.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Request a demo',
                        'heading' => 'See Cadence on your own pipeline',
                        'summary' => 'Email sales@cadencecrm.example or use the form below. A solutions engineer replies within one working day and maps Cadence to how you sell.',
                        'actions' => [
                            ['label' => 'Email sales', 'url' => 'mailto:sales@cadencecrm.example', 'style' => 'primary'],
                            ['label' => 'Explore the product', 'url' => '#product', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Talk to the Cadence team',
                    ],
                    $this->newsletterSection(
                        heading: 'Prefer the short version first?',
                        summary: 'Get the Cadence revenue brief while you wait — pipeline operations and automation patterns, monthly.',
                    ),
                    $this->integrationsReportingSection(
                        heading: 'We will connect to the tools you already use',
                        summary: 'Bring your email, calendar, support desk, and finance system — we map them in the demo.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready when you are',
                        summary: 'Send your sales process over and we will come back within one working day with a tailored walkthrough.',
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
            title: 'No matching records — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No records match that view',
                'A graceful empty state for a filtered CRM view with no matching records.',
            ),
            renderData: [
                'summary' => 'No records match that filter yet — but Cadence can still point your team somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Saved view',
                        'heading' => 'No records match this view — yet',
                        'summary' => 'Nothing matches the current filters. Clear them to see the full pipeline, or adjust the view to widen the net.',
                        'actions' => [
                            ['label' => 'Clear filters', 'url' => '#product', 'style' => 'primary'],
                            ['label' => 'Request a demo', 'url' => '#contact', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing to show here',
                        'summary' => 'When records match this view they will appear here, newest activity first.',
                        'items' => [],
                    ],
                    $this->workflowAutomationSection(
                        heading: 'While the view is empty',
                        summary: 'The automations that keep records flowing into views like this one.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific account?',
                        summary: 'Book a demo and we will show how Cadence views and filters surface exactly the records you need.',
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
                'A not-found page that routes visitors back into the Cadence product and demo paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into Cadence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This record could not be found',
                        'summary' => 'The link is broken or the page has moved. Head back to the product, or talk to our team.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Explore the product', 'url' => '#product', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and we will point you to the right part of Cadence.',
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
            name: self::BRAND . ' Demo',
            title: 'Book a demo — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Ready to give your revenue team one source of truth?',
                'A focused conversion page inviting teams to book a Cadence CRM demo.',
            ),
            renderData: [
                'summary' => 'Ready to give your revenue team one source of truth? Book a Cadence demo.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Book a demo',
                        'heading' => 'Give your revenue team one source of truth',
                        'summary' => 'Whether you are replacing a legacy CRM or graduating from spreadsheets, Cadence brings dashboards, automation, and reporting into one workspace.',
                        'actions' => [
                            ['label' => 'Request a demo', 'url' => '#contact', 'style' => 'primary'],
                            ['label' => 'Email sales', 'url' => 'mailto:sales@cadencecrm.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Cadence CRM workspace',
                    ],
                    $this->proofSection(
                        heading: 'Why revenue teams choose Cadence',
                        summary: 'The numbers behind the switch.',
                    ),
                    $this->customerStoriesSection(
                        heading: 'Teams already running on Cadence',
                        summary: 'Operators who consolidated their stack into one workspace.',
                    ),
                    $this->ctaSection(
                        heading: 'One demo away',
                        summary: 'Send your sales process over and we will come back within one working day with a tailored walkthrough.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function productDashboardSection(string $heading, string $summary): array
    {
        return [
            'type' => 'product-dashboard',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Pipeline at a glance', 'summary' => 'Every open deal by stage, weighted by likelihood, refreshed the moment a rep moves a card.'],
                ['title' => 'Forecast you can defend', 'summary' => 'Roll-ups by team, region, and quarter that match what reps actually see in their own boards.'],
                ['title' => 'Activity that explains the number', 'summary' => 'Calls, emails, and meetings tied to each deal so the forecast is never a black box.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dataModelSection(string $heading, string $summary): array
    {
        return [
            'type' => 'data-model',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Companies', 'summary' => 'Accounts with ownership, segment, and health, linked to every contact and deal beneath them.'],
                ['title' => 'Contacts', 'summary' => 'People with roles, history, and consent state, deduplicated as your team works.'],
                ['title' => 'Deals', 'summary' => 'Opportunities with stage, value, and next step — the unit every report is built on.'],
                ['title' => 'Activities', 'summary' => 'Calls, emails, notes, and tasks captured automatically and attached to the right record.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function workflowAutomationSection(string $heading, string $summary): array
    {
        return [
            'type' => 'workflow-automation',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Lead routing', 'summary' => 'Assign inbound leads by territory and round-robin the instant they land, so nothing sits unclaimed.', 'meta' => 'Trigger: new lead'],
                ['title' => 'Follow-up sequences', 'summary' => 'Queue the next touch when a deal stalls, then pause the moment the prospect replies.', 'meta' => 'Trigger: deal idle 5 days'],
                ['title' => 'Record hygiene', 'summary' => 'Fill missing fields, flag stale deals, and keep stages honest without a rep lifting a finger.', 'meta' => 'Trigger: nightly'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function collaborationSection(string $heading, string $summary): array
    {
        return [
            'type' => 'collaboration',
            'heading' => $heading,
            'summary' => $summary,
            'url' => '#contact',
            'label' => 'See collaboration in a demo',
            'items' => [
                ['title' => 'Deal rooms', 'summary' => 'A shared space per opportunity where sales, success, and marketing leave context that survives handoffs.'],
                ['title' => '@mentions & notes', 'summary' => 'Loop in the right colleague on the record itself instead of forwarding a thread no one can find later.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function integrationsReportingSection(string $heading, string $summary): array
    {
        return [
            'type' => 'integrations-reporting',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Email & calendar sync', 'summary' => 'Two-way sync logs every conversation and meeting against the right deal automatically.', 'meta' => 'Inbox'],
                ['title' => 'Support & finance', 'summary' => 'Pull tickets and invoices onto the account so reps see the whole relationship, not just the pipeline.', 'meta' => 'Stack'],
                ['title' => 'Reporting & exports', 'summary' => 'Scheduled reports and warehouse exports keep leadership and BI on the same numbers as the floor.', 'meta' => 'Reporting'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function customerStoriesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'customer-stories',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Northwind — field services', 'summary' => 'Replaced a spreadsheet, a help desk, and a legacy CRM with one workspace and lifted win rate by 22%.'],
                ['title' => 'Harbour & Co. — wholesale', 'summary' => 'Cut deal admin in half with routing and follow-up automation, freeing reps for live accounts.'],
                ['title' => 'Meridian — B2B SaaS', 'summary' => 'Gave leadership a forecast they finally trusted by tying every number back to logged activity.'],
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
            'action' => '#contact',
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
                ['value' => '22%', 'label' => 'Average win-rate lift in the first two quarters on Cadence.'],
                ['value' => '3 tools', 'label' => 'Replaced on average when teams consolidate onto one workspace.'],
                ['value' => '1 day', 'label' => 'Reply to every demo request from a real solutions engineer.'],
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
                ['category' => 'Automation', 'title' => 'Branching sequences', 'summary' => 'Follow-up flows can now branch on reply sentiment and deal stage, not just time.'],
                ['category' => 'Reporting', 'title' => 'Cohort forecasting', 'summary' => 'Forecast roll-ups now segment by acquisition cohort so leadership sees trend, not just total.'],
                ['category' => 'Integrations', 'title' => 'Finance sync', 'summary' => 'Invoices and payment status now sync onto accounts so reps see the full relationship.'],
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
            'label' => 'Request a demo',
            'url' => '#contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'items' => [
                ['label' => 'Product', 'url' => '#product'],
                ['label' => 'Automation', 'url' => '#workflow-automation'],
                ['label' => 'Integrations', 'url' => '#integrations-reporting'],
                ['label' => 'Customers', 'url' => '#customer-stories'],
                ['label' => 'Updates', 'url' => '#updates'],
            ],
            'ctaLabel' => 'Request a demo',
            'ctaUrl' => '#contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'summary' => 'The product workspace revenue teams run on. Dashboards, automation, and reporting in one place.',
            'columns' => [
                [
                    'heading' => 'Product',
                    'title' => 'Product',
                    'links' => [
                        ['label' => 'Dashboards', 'url' => '#product'],
                        ['label' => 'Data model', 'url' => '#data-model'],
                        ['label' => 'Automation', 'url' => '#workflow-automation'],
                        ['label' => 'Reporting', 'url' => '#integrations-reporting'],
                    ],
                ],
                [
                    'heading' => 'Customers',
                    'title' => 'Customers',
                    'links' => [
                        ['label' => 'Customer stories', 'url' => '#customer-stories'],
                        ['label' => 'Product updates', 'url' => '#updates'],
                        ['label' => 'Integrations', 'url' => '#integrations-reporting'],
                    ],
                ],
                [
                    'heading' => 'Company',
                    'title' => 'Company',
                    'links' => [
                        ['label' => 'Request a demo', 'url' => '#contact'],
                        ['label' => 'sales@cadencecrm.example', 'url' => 'mailto:sales@cadencecrm.example'],
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

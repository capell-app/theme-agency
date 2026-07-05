<?php

declare(strict_types=1);

use Capell\ThemeStudio\NightShift\Support\Demo\NightShiftDemoContent;

/**
 * Proves the section-builder→layout-builder conversion did not silently
 * lose or alter any of the real, previously-authored Night Shift demo copy.
 *
 * Mirrors LiquidGlassBespokeWidgetsTest's copy-preservation coverage.
 * Rather than re-typing the pre-conversion strings a second time (which
 * would only prove this test file agrees with itself), every literal
 * heading/summary/title string below was extracted directly from the
 * pre-conversion `NightShiftDemoContent.php` blob at git revision
 * d82e48b10 (`packages/theme-night-shift/src/Support/Demo/NightShiftDemoContent.php`,
 * the last commit before this conversion touched the file — verify with
 * `git show d82e48b10:packages/theme-night-shift/src/Support/Demo/NightShiftDemoContent.php`).
 * This test asserts every one of those literals still appears, byte-for-byte,
 * in the post-conversion class source. If a future edit accidentally drops
 * or rewords a recovered string, this test fails.
 *
 * @return list<string>
 */
function nightShiftPreConversionCopyLiterals(): array
{
    return [
        'One dark surface for the whole product team',
        'Northwind System brings inboxes, issues, roadmaps, automation, integrations, and security into a single workspace teams operate in all day.',
        'Northwind System is the dark operating surface where product teams run workflows, automation, roadmaps, and security proof from one place.',
        'The dark operating surface your product team runs on',
        'Triage the inbox, move issues, ship the roadmap, and let automation handle the rest. Northwind System keeps every workflow in one fast, near-black workspace built for focus.',
        'A workspace built around how teams actually ship',
        'Three connected surfaces — work, planning, and automation — that keep engineers, PMs, and operators in the same context.',
        'Workflows that move work without the busywork',
        'Rails for triage, review, release, and incident response — each one a clear path through the system.',
        'Agents and automation that do the repetitive work',
        'Set the rules once. Automation routes issues, drafts updates, and closes the loop while the team stays focused.',
        'Planning and roadmap that stay honest',
        'Every roadmap item traces back to the work in flight, so the plan never drifts from reality.',
        'Changelog and integrations, always in sync',
        'Ship notes write themselves and every tool the team relies on is wired in.',
        'Security and trust, proven not promised',
        'SOC 2 Type II, SSO, audit logs, and granular roles — the controls security teams ask for first.',
        'Teams ship more once the system carries the load',
        'Outcomes from teams running on Northwind System.',
        'See your team in the system',
        'Book a 30-minute walkthrough and we will map your workflows onto Northwind System live.',
        'Every workflow in the system',
        'Browse the workflows, automations, and integrations teams run on Northwind System, organised by the surface they touch.',
        'A scannable index of the workflows, automation runs, and integrations available across Northwind System.',
        'Every workflow in the system, ready to run',
        'Filter by surface — inbox, issues, roadmap, automation, or security — and drop a workflow into your workspace in a click.',
        'Featured workflows',
        'The rails teams reach for first.',
        'More from the workflow library',
        'Automation recipes, integration templates, and security playbooks.',
        'Found a workflow that fits?',
        'Tell us how your team works today and we will wire the right workflows into a trial workspace.',
        'How Helios Robotics cut release lead time in half',
        'A 60-person hardware-software team moved triage, planning, and release onto Northwind System and shipped twice as often.',
        'How Helios Robotics consolidated triage, planning, and release onto Northwind System and halved release lead time.',
        'Helios Robotics cut release lead time in half',
        'A hardware-software team drowning in tool sprawl moved every workflow onto one dark surface — and watched the queue finally clear.',
        'What changed when the work moved into one system',
        'Three surfaces replaced six tools, and the handoffs between them disappeared.',
        'Automation took the repetitive load off the team',
        'Routing, triage, and status updates moved to automation so engineers stayed in flow.',
        'Planning the team could trust again',
        'The roadmap finally matched the work in flight, week over week.',
        'Want results like Helios?',
        'Most teams start with a guided pilot on one workflow. Tell us yours and we will scope it.',
        'Request a demo',
        'Tell us how your team works today and a solutions engineer will map your workflows onto Northwind System live.',
        'Request a demo and a solutions engineer will walk your team through the system on your own workflows.',
        'Request a demo of the product system',
        'What a demo actually covers',
        'Three surfaces, your data shape, and the security review — no fluff.',
        'The security questions, answered first',
        'The controls and evidence security teams ask for before a trial begins.',
        'Prefer to just follow along?',
        'Join the list and get changelog notes and workflow ideas before your first call.',
        'No workflows match that filter',
        'A graceful empty state for a filtered workflow library with no matching results.',
        'No workflows match that filter yet — but the system can still point your team somewhere useful.',
        'No workflows match that filter — yet',
        'Nothing is wired to this surface in your workspace. Clear the filter to see everything, or ask us to build the workflow you need.',
        'Nothing to show on this surface',
        'When workflows are wired to this surface they appear here, most recent first.',
        'Popular workflows to start from',
        'The rails most teams turn on in week one.',
        'Need a workflow that is not here?',
        'Describe how your team works and we will build the workflow into your trial.',
        'Page not found',
        'A not-found page that routes visitors back into the workflows and demo paths.',
        'That page has moved or never existed — here is the way back into the system.',
        'This route is not in the system',
        'The link is broken or the page has moved. Head back to the homepage, or talk to us about a demo.',
        'Still looking for something?',
        'Tell us what you needed and we will route you to the right surface.',
        'Ready to run your team on one system?',
        'A focused conversion page inviting teams to start a guided pilot on Northwind System.',
        'Ready to run your team on one system? Start a guided pilot on Northwind System.',
        'Run your whole team on one product system',
        'Move triage, planning, automation, and release onto a single dark surface — and give security the evidence they need on day one.',
        'The controls that clear procurement',
        'Everything security and IT review before a rollout.',
        'Why teams move to Northwind System',
        'The numbers behind the switch.',
        'One pilot away',
        'Start a guided pilot on a single workflow and expand once the team feels the difference.',
        'Work surface',
        'Inbox, issues, and reviews in one triage view, so nothing waits in a second tool.',
        'Planning surface',
        'Roadmaps and cycles that draw straight from the work in flight, never a stale copy.',
        'Automation surface',
        'Rules and agents that route, draft, and close work the moment conditions are met.',
        'Triage rail',
        'New issues land, get scored, and route to the right owner before standup.',
        'Review rail',
        'Pull requests and design reviews queue with context and clear next actions.',
        'Release rail',
        'Cut a release, generate notes, and notify every channel from one action.',
        'Incident rail',
        'Declare, assign, and timeline an incident without leaving the workspace.',
        'Triage agent',
        'Standup digest',
        'Release notes writer',
        'Now',
        'Inbox triage and review rails shipping to every workspace this cycle.',
        'Next',
        'Automation marketplace and shared agent templates for cross-team workflows.',
        'Later',
        'Custom security evidence packs and per-region data residency controls.',
        'Linked to your repos',
        'Two-way sync with GitHub and GitLab keeps issues, branches, and PRs in step.',
        'Wired into chat',
        'Slack and Teams notifications fire from the same rules that move the work.',
        'Auto-written changelog',
        'Every release ships with notes generated from the work that landed in it.',
        'SOC 2 Type II',
        'Independently audited controls, with the report available under NDA.',
        'SSO and SCIM',
        'SAML single sign-on and automated provisioning across your identity provider.',
        'Audit logs and roles',
        'Every action is logged, and granular roles keep access scoped to the work.',
        'Auto-route incoming issues',
        'Sync issues with GitHub',
        'Security review playbook',
        'Trust',
        'Company',
        'The dark operating surface for product teams. Workflows, automation, roadmaps, and security in one place.',
    ];
}

it('preserves every real, previously-authored heading and summary literal from before the layout-builder conversion', function (): void {
    $preConversionLiterals = nightShiftPreConversionCopyLiterals();

    $postConversionSource = (string) file_get_contents(dirname(__DIR__, 2) . '/src/Support/Demo/NightShiftDemoContent.php');

    $missingLiterals = array_values(array_filter(
        $preConversionLiterals,
        static fn (string $literal): bool => ! str_contains($postConversionSource, $literal),
    ));

    expect($missingLiterals)->toBe([]);
});

it('keeps sectionCopy() ordering and types identical to the pre-conversion render_data sections list for the homepage surface', function (): void {
    $content = new NightShiftDemoContent;

    $homepageCopy = $content->sectionCopy('homepage');

    expect(array_column($homepageCopy, 'type'))->toBe([
        'hero',
        'system-hero',
        'workflow-rails',
        'agents-automation',
        'planning-roadmap',
        'changelog-integrations',
        'security-proof',
        'proof',
        'cta',
    ]);
});

it('keeps sectionCopy() ordering and types identical to the pre-conversion render_data sections list for every other surface', function (): void {
    $content = new NightShiftDemoContent;

    expect(array_column($content->sectionCopy('directory'), 'type'))->toBe([
        'hero', 'workflow-rails', 'content-listing', 'cta',
    ]);

    expect(array_column($content->sectionCopy('detail'), 'type'))->toBe([
        'hero', 'system-hero', 'agents-automation', 'planning-roadmap', 'cta',
    ]);

    expect(array_column($content->sectionCopy('contact'), 'type'))->toBe([
        'hero', 'system-hero', 'security-proof', 'newsletter',
    ]);

    expect(array_column($content->sectionCopy('empty'), 'type'))->toBe([
        'hero', 'content-listing', 'workflow-rails', 'cta',
    ]);

    expect(array_column($content->sectionCopy('not-found'), 'type'))->toBe([
        'hero', 'cta',
    ]);

    expect(array_column($content->sectionCopy('cta'), 'type'))->toBe([
        'hero', 'security-proof', 'proof', 'cta',
    ]);

    expect($content->sectionCopy('unknown-surface'))->toBe([]);
});

it('preserves the empty surface\'s real, previously-authored inline empty-state listing copy for later widget wiring', function (): void {
    $content = new NightShiftDemoContent;

    expect($content->emptyStateListingCopy())->toBe([
        'type' => 'content-listing',
        'heading' => 'Nothing to show on this surface',
        'summary' => 'When workflows are wired to this surface they appear here, most recent first.',
        'items' => [],
    ]);
});

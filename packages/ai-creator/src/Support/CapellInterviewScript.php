<?php

declare(strict_types=1);

namespace Capell\AiCreator\Support;

/**
 * The deterministic interview script returned by `get_interview`. It is a
 * structured object the external agent reads — NOT prose for a model and NOT
 * inference. Three parts:
 *
 *  - required[]  — canonical-phrasing questions every client asks identically;
 *                  they gate delivery and only the human can answer them.
 *  - expansion[] — goals the agent satisfies by improvising, seeded by the
 *                  discovery tools + site.description (Capell supplies the menu,
 *                  the agent writes the copy).
 *  - rules       — invariants the tools enforce regardless of agent behaviour.
 *
 * Flow order: site_name → site_purpose → [agent: theme] → [agent: pages+copy]
 * → generate (validate_spec + build_preview) → delivery_target (+ follow-up).
 */
final class CapellInterviewScript
{
    /**
     * Bump when the canonical required-core phrasing changes, so clients can
     * detect drift in the fixed wording.
     */
    public const int VERSION = 1;

    /**
     * @return array<string, mixed>
     */
    public static function toArray(): array
    {
        return [
            'version' => self::VERSION,
            'flow' => ['site_name', 'site_purpose', 'theme', 'pages', 'generate', 'delivery_target'],
            'required' => [
                [
                    'key' => 'site_name',
                    'spec_field' => 'site.name',
                    'prompt' => "What's your site called?",
                    'type' => 'string',
                ],
                [
                    'key' => 'site_purpose',
                    'spec_field' => 'site.description',
                    'prompt' => 'What does your site do?',
                    'type' => 'string',
                    'note' => 'Also seeds the agent inference for theme and pages.',
                ],
                [
                    'key' => 'delivery_target',
                    'spec_field' => null,
                    'prompt' => 'How do you want it — a preview, saved to a local project, or deployed to a Capell cloud?',
                    'type' => 'enum',
                    'options' => ['preview', 'local', 'cloud'],
                    'ask_at' => 'close',
                    'follow_ups' => [
                        [
                            'when' => 'local',
                            'key' => 'project_name',
                            'prompt' => 'What should the local project be called?',
                            'tool' => 'export_site',
                        ],
                        [
                            'when' => 'cloud',
                            'key' => 'auth',
                            'prompt' => 'Sign in or claim a Capell account to deploy.',
                            'tool' => 'deploy_site',
                        ],
                    ],
                ],
            ],
            'expansion' => [
                [
                    'key' => 'theme',
                    'goal' => 'Pick and brand a theme.',
                    'spec_field' => 'theme',
                    'discovery' => ['list_themes'],
                    'guidance' => 'Propose a theme from list_themes; ask for a brand colour if useful; defaults are allowed.',
                ],
                [
                    'key' => 'pages',
                    'goal' => 'Propose a page set and write the copy.',
                    'spec_field' => 'pages',
                    'discovery' => ['list_page_types', 'list_section_types'],
                    'guidance' => 'Propose pages from site.description + list_page_types; the agent writes the copy; choose section types from list_section_types; confirm with the user.',
                ],
            ],
            'rules' => [
                'Always validate_spec before any build.',
                'Always offer build_preview before an irreversible cloud deploy.',
            ],
        ];
    }
}

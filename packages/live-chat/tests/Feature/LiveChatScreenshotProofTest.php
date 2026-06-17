<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\LiveChat\Data\LiveChatWidgetConfigData;
use Illuminate\Support\Facades\View;
use Symfony\Component\Process\Process;

it('keeps committed Live Chat widget and operator proof screenshots current', function (): void {
    View::addNamespace('capell-live-chat', __DIR__ . '/../../resources/views');

    $packagePath = dirname(__DIR__, 2);
    $repositoryPath = dirname(__DIR__, 4);
    $entries = [
        [
            'htmlPath' => $packagePath . '/docs/screenshots/.live-chat-widget.html',
            'screenshotPath' => $packagePath . '/docs/screenshots/live-chat-widget.png',
            'html' => liveChatWidgetScreenshotHtml(),
            'width' => 1280,
            'height' => 900,
            'click' => '[data-clc-launcher]',
            'expected' => 'Ask Capell Support',
        ],
        [
            'htmlPath' => $packagePath . '/docs/screenshots/.live-chat-conversations-admin.html',
            'screenshotPath' => $packagePath . '/docs/screenshots/live-chat-conversations-admin.png',
            'html' => liveChatOperatorScreenshotHtml(),
            'width' => 1280,
            'height' => 900,
            'click' => null,
            'expected' => 'Conversation inbox',
        ],
    ];

    foreach ($entries as $entry) {
        expect($entry['html'])
            ->toContain($entry['expected'])
            ->not->toContain('authoring')
            ->not->toContain('recordKey')
            ->not->toContain('capell-app/live-chat');

        if (getenv('CAPELL_REFRESH_LIVE_CHAT_SCREENSHOTS') === '1') {
            file_put_contents($entry['htmlPath'], $entry['html']);

            try {
                $arguments = [
                    'node',
                    $repositoryPath . '/scripts/capture-static-html-screenshot.mjs',
                    $entry['htmlPath'],
                    $entry['screenshotPath'],
                    (string) $entry['width'],
                    (string) $entry['height'],
                ];

                if (is_string($entry['click'])) {
                    $arguments[] = $entry['click'];
                }

                $process = new Process($arguments, $repositoryPath);
                $process->setTimeout(60);
                $process->mustRun();
            } finally {
                if (is_file($entry['htmlPath'])) {
                    unlink($entry['htmlPath']);
                }
            }
        }

        expect($entry['screenshotPath'])->toBeFile();

        $dimensions = getimagesize($entry['screenshotPath']);

        expect($dimensions)->toBeArray()
            ->and($dimensions[0] ?? null)->toBe($entry['width'])
            ->and($dimensions[1] ?? null)->toBeGreaterThanOrEqual($entry['height']);
    }
});

function liveChatWidgetScreenshotHtml(): string
{
    $config = new LiveChatWidgetConfigData(
        enabled: true,
        agentName: 'Capell Support',
        avatarInitials: 'CS',
        brandName: 'Ask Capell Support',
        welcomeMessage: 'Hi, I can help with pricing, implementation questions, or routing this to a person.',
        aiDisclosure: 'AI-assisted replies with human handoff available.',
        messagePlaceholder: 'Ask about packages, implementation, or support...',
        messageFirstLabel: 'Message first',
        detailsFirstLabel: 'Share details',
        handoffLabel: 'Request handoff',
        statusMessage: 'We usually reply in a few minutes.',
        startUrl: '/live-chat/conversations',
        messageUrl: '/live-chat/conversations/{conversation}/messages',
        handoffUrl: '/live-chat/conversations/{conversation}/handoff',
        branding: [
            'primary' => '#087765',
            'surface' => '#fcfffb',
            'text' => '#101715',
        ],
        labels: [
            'close' => 'Close',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'company' => 'Company',
            'send' => 'Send',
        ],
    );

    $script = view('capell-live-chat::script', ['config' => $config])->render();
    $payload = e(json_encode($config->toArray(), JSON_THROW_ON_ERROR | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG));

    return <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Live Chat widget proof</title>
    <style>
        body { min-height: 900px; margin: 0; background: linear-gradient(135deg, #f7fbf8, #e7efeb); font-family: system-ui, sans-serif; }
        main { max-width: 900px; padding: 72px; color: #101715; }
        h1 { max-width: 680px; font-size: 56px; line-height: 1; margin: 0 0 18px; }
        p { max-width: 620px; color: #52615b; font-size: 18px; line-height: 1.7; }
    </style>
</head>
<body>
    <main>
        <h1>Qualified conversations without custom widget code</h1>
        <p>Live Chat adds an AI-assisted public widget, details-first capture, human handoff, and Contacts sync while keeping the browser response free of internal message IDs.</p>
    </main>
    <div data-capell-live-chat-widget data-config="{$payload}"></div>
    <script>{$script}</script>
</body>
</html>
HTML;
}

function liveChatOperatorScreenshotHtml(): string
{
    return <<<'HTML'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Live Chat operator proof</title>
    <style>
        body { margin: 0; background: #f6f8f7; color: #101715; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        .shell { padding: 36px; }
        .header { display: flex; justify-content: space-between; align-items: flex-end; gap: 24px; margin-bottom: 24px; }
        h1 { margin: 0; font-size: 34px; line-height: 1.1; }
        .sub { margin-top: 8px; color: #5b6762; }
        .button { border-radius: 8px; background: #087765; color: white; padding: 11px 14px; font-weight: 700; }
        .grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 18px; }
        .panel { border: 1px solid #d9e2dc; border-radius: 10px; background: white; box-shadow: 0 18px 45px rgba(16, 23, 21, .08); }
        .panel h2 { margin: 0; padding: 18px 20px; border-bottom: 1px solid #e6ece8; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 14px 16px; border-bottom: 1px solid #edf1ee; text-align: left; vertical-align: top; }
        th { color: #66736d; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
        .badge { display: inline-flex; border-radius: 999px; padding: 4px 9px; background: #fff7ed; color: #9a3412; font-weight: 700; font-size: 12px; }
        .badge.green { background: #ecfdf5; color: #047857; }
        .thread { display: grid; gap: 10px; padding: 18px; }
        .message { max-width: 88%; border-radius: 10px; padding: 11px 12px; line-height: 1.45; }
        .visitor { justify-self: end; background: #087765; color: white; }
        .assistant { justify-self: start; background: #f4f7f5; border: 1px solid #dce5df; }
        .meta { color: #64716b; font-size: 13px; }
        .cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 18px; }
        .card { border: 1px solid #d9e2dc; border-radius: 10px; background: white; padding: 16px; }
        .value { margin-top: 6px; font-size: 26px; font-weight: 800; }
    </style>
</head>
<body>
    <div class="shell">
        <div class="header">
            <div>
                <h1>Conversation inbox</h1>
                <div class="sub">Operators review visitor intent, AI confidence, handoff state, and CRM context.</div>
            </div>
            <div class="button">Reply to visitor</div>
        </div>
        <div class="cards">
            <div class="card"><div class="meta">Open conversations</div><div class="value">18</div></div>
            <div class="card"><div class="meta">Waiting for human</div><div class="value">5</div></div>
            <div class="card"><div class="meta">Qualified leads</div><div class="value">11</div></div>
        </div>
        <div class="grid">
            <section class="panel">
                <h2>Incoming conversations</h2>
                <table>
                    <thead><tr><th>Visitor</th><th>Intent</th><th>Status</th><th>Latest message</th></tr></thead>
                    <tbody>
                        <tr><td>Ada Lovelace<br><span class="meta">ada@example.com</span></td><td><span class="badge green">Sales</span></td><td>Waiting for human</td><td>Can you help price a marketplace rollout?</td></tr>
                        <tr><td>Grace Hopper<br><span class="meta">Enterprise account</span></td><td><span class="badge">Support</span></td><td>AI replied</td><td>Need implementation docs for external embeds.</td></tr>
                        <tr><td>Website visitor<br><span class="meta">No contact yet</span></td><td>General</td><td>Collecting details</td><td>Do you support after-hours routing?</td></tr>
                    </tbody>
                </table>
            </section>
            <section class="panel">
                <h2>Selected thread</h2>
                <div class="thread">
                    <div class="message assistant">Hi, I can help with package setup, pricing, or route this to a person.</div>
                    <div class="message visitor">Can you help price a marketplace rollout?</div>
                    <div class="message assistant">Yes. I can collect scope and hand this to the growth team.</div>
                    <div class="message visitor">Please have someone follow up today.</div>
                    <div class="meta">Risk: elevated · Lead qualification: qualified lead · Sources: Pricing overview, Marketplace rollout guide</div>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
HTML;
}

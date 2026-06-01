<?php

declare(strict_types=1);

use Capell\SeoSuite\Actions\RenderContentMarkdownAction;

it('renders null, plain text, arrays, and json content to markdown', function (): void {
    $jsonContent = json_encode(['<p>JSON copy</p>', '<p>Second block</p>'], JSON_THROW_ON_ERROR);

    expect(RenderContentMarkdownAction::run(null))->toBe('')
        ->and(RenderContentMarkdownAction::run('  Plain copy  '))->toBe('Plain copy')
        ->and(RenderContentMarkdownAction::run(['<h2>Intro</h2>', ['<p>Nested copy</p>'], 123]))->toBe("## Intro\n\nNested copy")
        ->and(RenderContentMarkdownAction::run($jsonContent))->toBe("JSON copy\n\nSecond block");
});

it('renders common html nodes to clean markdown', function (): void {
    $html = <<<'HTML'
        <section>
            <h1>Page title</h1>
            <p>Intro <a href="https://example.test">link</a><br>Next line</p>
            <ul><li>First item</li><li><a href="">Second item</a></li></ul>
            <ol><li>Step one</li><li>Step two</li></ol>
            <span>Inline note</span>
        </section>
    HTML;

    expect(RenderContentMarkdownAction::run($html))->toBe(
        "# Page title\n\nIntro [link](https://example.test)\nNext line\n\n- First item\n- Second item\n\n1. Step one\n2. Step two\n Inline note",
    );
});

it('drops empty html links while preserving link text without hrefs', function (): void {
    expect(RenderContentMarkdownAction::run('<p><a href="https://example.test"></a><a>Visible text</a></p>'))
        ->toBe('Visible text');
});

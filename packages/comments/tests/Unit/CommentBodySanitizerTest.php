<?php

declare(strict_types=1);

use Capell\Comments\Support\CommentBodySanitizer;

it('strips invisible control characters while preserving readable text spacing', function (): void {
    $sanitizer = new CommentBodySanitizer;

    $body = "Safe\u{200B} text \u{202E}<strong>visible</strong>\x07\n\n\nnext";

    expect($sanitizer->sanitize($body))->toBe("Safe text visible\n\nnext");
});

it('counts scheme, www, and bare-domain links without counting email domains', function (): void {
    $sanitizer = new CommentBodySanitizer;

    $body = 'Visit https://one.test, http://two.test/path, www.three.test, four.example/deal, and email me@example.com.';

    expect($sanitizer->linkCount($body))->toBe(4);
});

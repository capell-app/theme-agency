<?php

declare(strict_types=1);

use Capell\Tests\Support\Concerns\TestingFrontend;

require_once __DIR__ . '/../../Support/ThemeDemoLayoutScreenshots.php';

uses(TestingFrontend::class);

it('captures foundation default frontend demo page screenshots for the expected page types and layouts', function (): void {
    $pages = installFoundationThemeDemoScreenshotFixture();

    assertThemeDemoLayoutScreenshots('default', $pages, [
        'homepage' => ['type' => 'home', 'layout' => 'home'],
        'directory' => ['type' => 'default', 'layout' => 'results'],
        'detail' => ['type' => 'default', 'layout' => 'default'],
        'contact' => ['type' => 'default', 'layout' => 'system'],
        'empty' => ['type' => 'default', 'layout' => 'default'],
        'not-found' => ['type' => 'error', 'layout' => 'system'],
        'maintenance' => ['type' => 'maintenance', 'layout' => 'system'],
        'system' => ['type' => 'system', 'layout' => 'system'],
        'cta' => ['type' => 'default', 'layout' => 'default'],
    ]);
});

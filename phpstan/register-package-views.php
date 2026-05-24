<?php

declare(strict_types=1);

$viewFactory = app('view');
$packageViewDirectories = glob(__DIR__ . '/../packages/*/resources/views') ?: [];

foreach ($packageViewDirectories as $viewDirectory) {
    $packageName = basename(dirname(dirname($viewDirectory)));
    $viewFactory->addNamespace('capell-' . $packageName, $viewDirectory);
}

$sharedCapellViewDirectories = [
    __DIR__ . '/../packages/frontend-authoring/resources/views',
    __DIR__ . '/../packages/foundation-theme/resources/views',
    __DIR__ . '/../packages/seo-suite/resources/views',
    __DIR__ . '/../packages/site-discovery/resources/views',
];

foreach ($sharedCapellViewDirectories as $viewDirectory) {
    if (is_dir($viewDirectory)) {
        $viewFactory->addNamespace('capell', $viewDirectory);
    }
}

$navigationTestViews = __DIR__ . '/../packages/navigation/tests/Fixtures/FoundationTheme/components/resources/views';
if (is_dir($navigationTestViews)) {
    $viewFactory->addNamespace('capell-navigation-test', $navigationTestViews);
}

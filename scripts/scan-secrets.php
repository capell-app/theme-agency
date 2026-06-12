<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (is_file($autoload)) {
    require_once $autoload;
}

$root = dirname(__DIR__);
$finder = (new Finder)
    ->files()
    ->in($root)
    ->exclude([
        '.git',
        'coverage',
        'node_modules',
        'storage',
        'vendor',
    ])
    ->ignoreDotFiles(false)
    ->ignoreVCS(true);

$patterns = [
    'AWS access key' => '/AKIA[0-9A-Z]{16}/',
    'GitHub token' => '/gh[pousr]_[A-Za-z0-9_]{36,}/',
    'OpenAI key' => '/sk-[A-Za-z0-9]{32,}/',
    'Stripe live secret key' => '/sk_live_[A-Za-z0-9]{16,}/',
    'Private key block' => '/-----BEGIN (?:RSA |EC |OPENSSH |)PRIVATE KEY-----/',
];

$allowlistedPaths = [
    'docs/package-security-checklist.md',
    'scripts/scan-secrets.php',
];

$findings = [];

foreach ($finder as $file) {
    $relativePath = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());

    if (in_array($relativePath, $allowlistedPaths, true)) {
        continue;
    }

    $contents = $file->getContents();

    foreach ($patterns as $label => $pattern) {
        if (preg_match($pattern, $contents, $match, PREG_OFFSET_CAPTURE) !== 1) {
            continue;
        }

        $line = substr_count(substr($contents, 0, (int) $match[0][1]), "\n") + 1;
        $findings[] = sprintf('%s:%d contains possible %s', $relativePath, $line, $label);
    }
}

if ($findings !== []) {
    fwrite(STDERR, "Secret scan failed:\n" . implode("\n", $findings) . "\n");

    return 1;
}

fwrite(STDOUT, "Secret scan passed.\n");

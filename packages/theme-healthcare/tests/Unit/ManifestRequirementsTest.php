<?php

declare(strict_types=1);
use Illuminate\Support\Facades\File;

describe('theme healthcare capell.json manifest', function (): void {
    it('declares its demo command for package demo installs', function (): void {
        $manifest = json_decode(
            File::get(__DIR__ . '/../../capell.json'),
            associative: true,
        );

        expect($manifest['commands']['demo'])->toBe('capell:theme-healthcare-demo')
            ->and($manifest['commands']['demoParams'])->toBe(['url', 'languages', 'sites']);
    });
});

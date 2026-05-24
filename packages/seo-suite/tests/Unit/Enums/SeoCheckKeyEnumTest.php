<?php

declare(strict_types=1);

use Capell\SeoSuite\Enums\SeoCheckKeyEnum;

it('exposes labels for every seo check key', function (): void {
    foreach (SeoCheckKeyEnum::cases() as $checkKey) {
        expect($checkKey->getLabel())->toBeString()
            ->and($checkKey->getLabel())->not->toBe('')
            ->and($checkKey->getLabel())->not->toStartWith('capell');
    }
});

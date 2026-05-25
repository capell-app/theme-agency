<?php

declare(strict_types=1);

use Capell\Comments\Enums\CommentPublicationPolicy;
use Capell\Comments\Settings\CommentSettings;
use Capell\Comments\Support\CommentSettingsResolver;

it('resolves site and commentable settings overrides', function (): void {
    /** @var CommentSettings $settings */
    $settings = (new ReflectionClass(CommentSettings::class))->newInstanceWithoutConstructor();
    $settings->enabled = true;
    $settings->publication_policy = CommentPublicationPolicy::RequireApproval->value;
    $settings->max_depth = 4;
    $settings->commentable_type_overrides = [
        [
            'commentable_type' => 'article',
            'publication_policy' => CommentPublicationPolicy::AutoPublish->value,
            'max_depth' => '2',
        ],
    ];
    $settings->site_overrides = [
        [
            'site_id' => '10',
            'enabled' => '0',
            'max_depth' => '1',
        ],
    ];

    app()->instance(CommentSettings::class, $settings);

    $resolver = resolve(CommentSettingsResolver::class);

    expect($resolver->publicationPolicy(10, 'article'))->toBe(CommentPublicationPolicy::AutoPublish)
        ->and($resolver->maxDepth(10, 'article'))->toBe(1)
        ->and($resolver->enabled(10, 'article'))->toBeFalse()
        ->and($resolver->maxDepth(11, 'article'))->toBe(2);
});

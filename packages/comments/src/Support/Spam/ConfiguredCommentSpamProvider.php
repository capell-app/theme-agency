<?php

declare(strict_types=1);

namespace Capell\Comments\Support\Spam;

use Capell\Comments\Contracts\CommentSpamProvider;
use Capell\Comments\Data\CommentSpamCheckData;
use Capell\Comments\Data\CommentSpamScoreData;
use Illuminate\Contracts\Container\Container;

final class ConfiguredCommentSpamProvider implements CommentSpamProvider
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function score(CommentSpamCheckData $check): CommentSpamScoreData
    {
        $reasons = [];
        $linkCount = $check->linkCount;

        foreach ($this->providerClasses() as $providerClass) {
            $score = $this->container->make($providerClass)->score($check);
            $linkCount = max($linkCount, $score->linkCount);
            $reasons = [
                ...$reasons,
                ...$score->reasons,
            ];
        }

        return new CommentSpamScoreData(
            linkCount: $linkCount,
            reasons: array_values(array_unique($reasons)),
        );
    }

    /**
     * @return list<class-string<CommentSpamProvider>>
     */
    private function providerClasses(): array
    {
        $providers = config('capell-comments.spam.providers', [LocalCommentSpamProvider::class]);

        if (! is_array($providers)) {
            return [LocalCommentSpamProvider::class];
        }

        $providerClasses = [];

        foreach ($providers as $provider) {
            if (! is_string($provider) || $provider === self::class || ! is_subclass_of($provider, CommentSpamProvider::class)) {
                continue;
            }

            $providerClasses[] = $provider;
        }

        return $providerClasses !== [] ? array_values(array_unique($providerClasses)) : [LocalCommentSpamProvider::class];
    }
}

<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Data;

use Capell\SocialFeeds\Enums\SocialFeedLayout;
use Illuminate\Support\Arr;

final readonly class SocialFeedWidgetConfigData
{
    public function __construct(
        public ?int $connectionId,
        public ?string $provider,
        public SocialFeedLayout $layout,
        public int $limit,
        public int $pageSize,
        public int $columns,
        public bool $showCaption,
        public bool $showAuthor,
        public bool $showDate,
        public bool $showMedia,
        public bool $autoplay,
        public int $transitionMs,
        public string $aspectRatio,
        public string $mediaAltStrategy,
        public string $emptyState,
    ) {}

    /**
     * @param  array<string, mixed>  $state
     */
    public static function fromState(array $state): self
    {
        $maxLimit = max(1, (int) config('capell-social-feeds.max_limit', 48));
        $limit = self::clamp((int) Arr::get($state, 'limit', config('capell-social-feeds.default_limit', 12)), 1, $maxLimit);
        $pageSize = self::clamp((int) Arr::get($state, 'page_size', 6), 1, $limit);

        return new self(
            connectionId: is_numeric(Arr::get($state, 'connection_id')) ? (int) Arr::get($state, 'connection_id') : null,
            provider: is_string(Arr::get($state, 'provider')) && Arr::get($state, 'provider') !== '' ? Arr::get($state, 'provider') : null,
            layout: SocialFeedLayout::fromState(Arr::get($state, 'layout')),
            limit: $limit,
            pageSize: $pageSize,
            columns: self::clamp((int) Arr::get($state, 'columns', 3), 1, 6),
            showCaption: (bool) Arr::get($state, 'show_caption', true),
            showAuthor: (bool) Arr::get($state, 'show_author', true),
            showDate: (bool) Arr::get($state, 'show_date', true),
            showMedia: (bool) Arr::get($state, 'show_media', true),
            autoplay: (bool) Arr::get($state, 'autoplay', false),
            transitionMs: self::clamp((int) Arr::get($state, 'transition_ms', 450), 100, 5000),
            aspectRatio: self::aspectRatio(Arr::get($state, 'aspect_ratio', 'square')),
            mediaAltStrategy: self::mediaAltStrategy(Arr::get($state, 'media_alt_strategy', 'auto')),
            emptyState: self::emptyState(Arr::get($state, 'empty_state', 'hidden')),
        );
    }

    public function mediaAltText(SocialFeedRenderItemData $item): string
    {
        if ($this->mediaAltStrategy === 'decorative') {
            return '';
        }

        $caption = self::compactText($item->text);

        if ($this->mediaAltStrategy === 'auto' && $this->showCaption && $caption !== null) {
            return '';
        }

        if ($caption !== null) {
            return self::limitText($caption);
        }

        $authorName = self::compactText($item->authorName);

        if ($authorName !== null) {
            return sprintf('Social post image by %s', self::limitText($authorName, 80));
        }

        return sprintf('%s social post image', ucfirst($item->provider));
    }

    private static function clamp(int $value, int $min, int $max): int
    {
        return min(max($value, $min), $max);
    }

    private static function aspectRatio(mixed $value): string
    {
        return in_array($value, ['square', 'landscape', 'portrait', 'natural'], true) ? (string) $value : 'square';
    }

    private static function mediaAltStrategy(mixed $value): string
    {
        return in_array($value, ['auto', 'caption', 'decorative'], true) ? (string) $value : 'auto';
    }

    private static function emptyState(mixed $value): string
    {
        return in_array($value, ['hidden', 'message'], true) ? (string) $value : 'hidden';
    }

    private static function compactText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = preg_replace('/\s+/', ' ', trim(strip_tags($value)));

        return is_string($text) && $text !== '' ? $text : null;
    }

    private static function limitText(string $value, int $limit = 120): string
    {
        if (strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(substr($value, 0, max(1, $limit - 3))) . '...';
    }
}

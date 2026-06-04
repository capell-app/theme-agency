<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Providers\Drivers;

use Capell\SocialFeeds\Contracts\SocialFeedProvider;
use Capell\SocialFeeds\Data\SocialFeedPostData;
use Capell\SocialFeeds\Enums\SocialAuthStrategy;
use Capell\SocialFeeds\Enums\SocialFeedItemType;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

final class RssFeedProvider implements SocialFeedProvider
{
    public function key(): string
    {
        return 'rss';
    }

    public function label(): string
    {
        return 'RSS / Atom';
    }

    public function authStrategy(): SocialAuthStrategy
    {
        return SocialAuthStrategy::None;
    }

    /**
     * @return array<string, mixed>
     */
    public function credentialSchema(): array
    {
        return [
            'feed_url' => [
                'type' => 'url',
                'required' => true,
            ],
        ];
    }

    /**
     * @return array<int, SocialFeedPostData>
     */
    public function fetch(SocialFeedConnection $connection, int $limit): array
    {
        $feedUrl = Arr::get($connection->credentials ?? [], 'feed_url');

        if (! is_string($feedUrl) || $feedUrl === '') {
            return [];
        }

        $response = Http::timeout((int) config('capell-social-feeds.http_timeout', 10))
            ->connectTimeout((int) config('capell-social-feeds.http_connect_timeout', 3))
            ->accept('application/rss+xml, application/atom+xml, application/xml, text/xml')
            ->get($feedUrl);

        if (! $response->successful()) {
            return [];
        }

        return array_slice($this->parseFeed($response->body(), $connection->name), 0, $limit);
    }

    /**
     * @return array<int, SocialFeedPostData>
     */
    private function parseFeed(string $xml, string $fallbackAuthor): array
    {
        $previousEntityLoader = libxml_disable_entity_loader(true);
        $previousErrors = libxml_use_internal_errors(true);

        try {
            $feed = simplexml_load_string(trim($xml), SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);
            libxml_disable_entity_loader($previousEntityLoader);
        }

        if (! $feed instanceof SimpleXMLElement) {
            return [];
        }

        if (property_exists($feed->channel, 'item') && $feed->channel->item !== null) {
            return $this->parseRssItems($feed, $fallbackAuthor);
        }

        if (property_exists($feed, 'entry') && $feed->entry !== null) {
            return $this->parseAtomEntries($feed, $fallbackAuthor);
        }

        return [];
    }

    /**
     * @return array<int, SocialFeedPostData>
     */
    private function parseRssItems(SimpleXMLElement $feed, string $fallbackAuthor): array
    {
        $items = [];

        foreach ($feed->channel->item as $item) {
            $link = trim((string) $item->link);
            $guid = trim((string) $item->guid);
            $description = trim(strip_tags((string) $item->description));
            $mediaUrl = $this->firstMediaUrl($item);

            $items[] = new SocialFeedPostData(
                externalId: $guid !== '' ? $guid : ($link !== '' ? $link : Str::uuid()->toString()),
                type: $mediaUrl !== null ? SocialFeedItemType::Image : ($link !== '' ? SocialFeedItemType::Link : SocialFeedItemType::Text),
                text: trim((string) $item->title) ?: $description,
                permalink: $link !== '' ? $link : null,
                mediaUrl: $mediaUrl,
                thumbnailUrl: $mediaUrl,
                authorName: trim((string) $item->author) ?: $fallbackAuthor,
                publishedAt: $this->parseDate((string) $item->pubDate),
                raw: ['source' => 'rss'],
            );
        }

        return $items;
    }

    /**
     * @return array<int, SocialFeedPostData>
     */
    private function parseAtomEntries(SimpleXMLElement $feed, string $fallbackAuthor): array
    {
        $items = [];

        foreach ($feed->entry as $entry) {
            $link = $this->atomLink($entry);
            $id = trim((string) $entry->id);
            $summary = trim(strip_tags((string) ($entry->summary ?: $entry->content)));

            $items[] = new SocialFeedPostData(
                externalId: $id !== '' ? $id : ($link ?? Str::uuid()->toString()),
                type: $link !== null ? SocialFeedItemType::Link : SocialFeedItemType::Text,
                text: trim((string) $entry->title) ?: $summary,
                permalink: $link,
                authorName: trim((string) $entry->author->name) ?: $fallbackAuthor,
                publishedAt: $this->parseDate((string) ($entry->published ?: $entry->updated)),
                raw: ['source' => 'atom'],
            );
        }

        return $items;
    }

    private function firstMediaUrl(SimpleXMLElement $item): ?string
    {
        if (property_exists($item, 'enclosure') && $item->enclosure !== null) {
            $url = (string) $item->enclosure->attributes()['url'];

            return $url !== '' ? $url : null;
        }

        $media = $item->children('media', true);

        if (property_exists($media, 'content') && $media->content !== null) {
            $url = (string) $media->content->attributes()['url'];

            return $url !== '' ? $url : null;
        }

        return null;
    }

    private function atomLink(SimpleXMLElement $entry): ?string
    {
        foreach ($entry->link as $link) {
            $href = (string) $link->attributes()['href'];

            if ($href !== '') {
                return $href;
            }
        }

        return null;
    }

    private function parseDate(string $value): ?CarbonImmutable
    {
        if (trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}

<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Providers\Drivers;

use Capell\SocialFeeds\Contracts\SocialFeedHostResolver;
use Capell\SocialFeeds\Contracts\SocialFeedProvider;
use Capell\SocialFeeds\Data\ResolvedFeedEndpointData;
use Capell\SocialFeeds\Data\SocialFeedPostData;
use Capell\SocialFeeds\Enums\SocialAuthStrategy;
use Capell\SocialFeeds\Enums\SocialFeedItemType;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use SimpleXMLElement;
use Throwable;

final class RssFeedProvider implements SocialFeedProvider
{
    public function __construct(private readonly ?SocialFeedHostResolver $hostResolver = null) {}

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

        $endpoint = $this->endpoint($feedUrl);

        $response = Http::timeout((int) config('capell-social-feeds.http_timeout', 10))
            ->connectTimeout((int) config('capell-social-feeds.http_connect_timeout', 3))
            ->withoutRedirecting()
            ->withHeaders(['Host' => $endpoint->hostHeader()])
            ->withOptions($this->requestOptions($endpoint))
            ->accept('application/rss+xml, application/atom+xml, application/xml, text/xml')
            ->get($endpoint->url);

        if (! $response->successful()) {
            return [];
        }

        return array_slice($this->parseFeed($response->body(), $connection->name), 0, $limit);
    }

    private function endpoint(string $url): ResolvedFeedEndpointData
    {
        $parts = parse_url($url);

        throw_if(! is_array($parts), InvalidArgumentException::class, 'Social feed URL must be an absolute HTTP URL.');

        $scheme = is_string($parts['scheme'] ?? null) ? strtolower($parts['scheme']) : null;
        $host = is_string($parts['host'] ?? null) ? strtolower($parts['host']) : null;

        throw_if(! in_array($scheme, ['https', 'http'], true) || $host === null || $host === '', InvalidArgumentException::class, 'Social feed URL must be an absolute HTTP URL.');

        $addresses = $this->resolvedHostAddresses($host);

        throw_if($addresses === [], InvalidArgumentException::class, 'Social feed URL host could not be resolved.');
        throw_if(! (bool) config('capell-social-feeds.allow_private_feed_urls', false) && $this->hasPrivateAddress($addresses), InvalidArgumentException::class, 'Social feed URL host is not allowed.');

        return new ResolvedFeedEndpointData(
            url: $url,
            scheme: $scheme,
            host: $host,
            port: $this->port($parts, $scheme),
            address: $addresses[0],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function requestOptions(ResolvedFeedEndpointData $endpoint): array
    {
        throw_unless(defined('CURLOPT_RESOLVE'), InvalidArgumentException::class, 'Social feed requests require cURL host pinning support.');

        return [
            'curl' => [
                CURLOPT_RESOLVE => [$endpoint->curlResolveEntry()],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    private function port(array $parts, string $scheme): int
    {
        $port = $parts['port'] ?? null;

        if (is_int($port) && $port > 0 && $port <= 65535) {
            return $port;
        }

        return $scheme === 'https' ? 443 : 80;
    }

    /**
     * @param  list<string>  $addresses
     */
    private function hasPrivateAddress(array $addresses): bool
    {
        foreach ($addresses as $address) {
            if ($this->isPrivateAddress($address)) {
                return true;
            }
        }

        return false;
    }

    private function isPrivateHostLabel(string $host): bool
    {
        return in_array($host, ['localhost', 'localhost.localdomain'], true) || str_ends_with($host, '.localhost');
    }

    private function isPrivateAddress(string $address): bool
    {
        return filter_var(
            $address,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ) === false;
    }

    /**
     * @return list<string>
     */
    private function resolvedHostAddresses(string $host): array
    {
        if ($this->isPrivateHostLabel($host)) {
            return ['127.0.0.1'];
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return [$host];
        }

        $addresses = ($this->hostResolver ?? resolve(SocialFeedHostResolver::class))->resolve($host);

        return array_values(collect($addresses)
            ->filter(static fn (string $address): bool => filter_var($address, FILTER_VALIDATE_IP) !== false)
            ->unique()
            ->values()
            ->all());
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

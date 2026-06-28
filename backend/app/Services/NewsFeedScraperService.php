<?php

namespace App\Services;

use App\Models\NewsFeedSource;
use App\Models\NewsStagingItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsFeedScraperService
{
    public function syncFeeds(): int
    {
        $sources = NewsFeedSource::where('is_active', true)->get();
        $imported = 0;

        foreach ($sources as $source) {
            try {
                $imported += $this->syncFeed($source->feed_url);
                $source->update(['last_synced_at' => now(), 'last_error' => null]);
            } catch (\Throwable $e) {
                Log::channel('analytics')->error("News feed sync failed for {$source->feed_url}: " . $e->getMessage());
                $source->update(['last_synced_at' => now(), 'last_error' => $e->getMessage()]);
            }
        }

        return $imported;
    }

    private function syncFeed(string $feedUrl): int
    {
        // Some sites' WAFs (Wordfence/Cloudflare) 403 generic bot UAs, so we
        // present as a regular browser to fetch what is otherwise public RSS.
        $response = Http::timeout(15)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept'     => 'application/rss+xml, application/xml, text/xml, */*;q=0.8',
            ])
            ->get($feedUrl);

        if (! $response->successful()) {
            throw new \RuntimeException("Feed returned HTTP {$response->status()}.");
        }

        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response->body());
        libxml_use_internal_errors($previous);

        if ($xml === false) {
            throw new \RuntimeException('Feed is not valid RSS/Atom XML.');
        }

        $rows = $xml->getName() === 'feed'
            ? $this->parseAtom($xml, $feedUrl)
            : $this->parseRss($xml, $feedUrl);

        if (empty($rows)) {
            return 0;
        }

        // Update only updated_at on conflict — never reset status/generated_post_id
        // for items the admin already reviewed (generated/dismissed) if the
        // same article reappears in a later feed pull.
        return NewsStagingItem::upsert($rows, ['article_url'], ['updated_at']);
    }

    private function parseRss(\SimpleXMLElement $xml, string $feedUrl): array
    {
        $sourceName = trim((string) ($xml->channel->title ?? $feedUrl));
        $rows = [];

        foreach ($xml->channel->item ?? [] as $item) {
            $link = trim((string) $item->link);
            if (blank($link)) {
                continue;
            }

            $rows[] = [
                'source_name'  => $sourceName,
                'source_url'   => $feedUrl,
                'article_url'  => $link,
                'title'        => trim((string) $item->title) ?: $link,
                'excerpt'      => $this->cleanExcerpt((string) $item->description),
                'published_at' => $this->parseDate((string) $item->pubDate),
                'status'       => 'new',
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        return $rows;
    }

    private function parseAtom(\SimpleXMLElement $xml, string $feedUrl): array
    {
        $sourceName = trim((string) ($xml->title ?? $feedUrl));
        $rows = [];

        foreach ($xml->entry ?? [] as $entry) {
            $link = $this->extractAtomLink($entry);
            if (blank($link)) {
                continue;
            }

            $rows[] = [
                'source_name'  => $sourceName,
                'source_url'   => $feedUrl,
                'article_url'  => $link,
                'title'        => trim((string) $entry->title) ?: $link,
                'excerpt'      => $this->cleanExcerpt((string) ($entry->summary ?? $entry->content ?? '')),
                'published_at' => $this->parseDate((string) ($entry->published ?? $entry->updated ?? '')),
                'status'       => 'new',
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        return $rows;
    }

    private function extractAtomLink(\SimpleXMLElement $entry): string
    {
        foreach ($entry->link as $link) {
            $rel = (string) ($link['rel'] ?? '');
            if ($rel === '' || $rel === 'alternate') {
                return (string) $link['href'];
            }
        }

        return '';
    }

    private function cleanExcerpt(string $raw): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($raw)) ?? '');

        return mb_substr($text, 0, 500);
    }

    private function parseDate(string $raw): ?Carbon
    {
        if (blank($raw)) {
            return null;
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable) {
            return null;
        }
    }
}

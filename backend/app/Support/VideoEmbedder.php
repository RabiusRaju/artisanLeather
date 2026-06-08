<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Turns plain YouTube / Vimeo / direct-video links found inside rich-text HTML
 * into inline, playable embeds (used for both the public blog page and the
 * admin "Video Preview" placeholder so both stay in sync).
 */
class VideoEmbedder
{
    private const YOUTUBE_PATTERN = '/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i';
    private const VIMEO_PATTERN = '/vimeo\.com\/(?:video\/)?(\d+)/i';
    private const DIRECT_VIDEO_PATTERN = '/\.(mp4|webm|ogg)(\?\S*)?$/i';
    private const ANY_URL_PATTERN = '/(https?:\/\/[^\s<]+)/i';

    public static function embed(?string $html): ?string
    {
        if (! $html || ! str_contains($html, 'http')) {
            return $html;
        }

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="__root">' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $root = $doc->getElementById('__root');
        if (! $root) {
            return $html;
        }

        static::replaceAnchors($doc, $root);
        static::replaceBareTextLinks($doc, $root);

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $doc->saveHTML($child);
        }

        return $result;
    }

    /**
     * Returns only the embed players found in the content (used for the
     * admin live-preview, which sits beside the editor rather than
     * duplicating the whole article body).
     */
    public static function extractEmbeds(?string $html): string
    {
        if (! $html || ! str_contains($html, 'http')) {
            return '';
        }

        preg_match_all(self::ANY_URL_PATTERN, $html, $matches);

        $doc = new DOMDocument();
        $seen = [];
        $output = '';

        foreach ($matches[1] ?? [] as $url) {
            $embed = static::buildEmbedNode($doc, rtrim($url, '"\'<>),.'));
            if (! $embed) {
                continue;
            }

            $key = $embed->getAttribute('style') . $embed->textContent
                . ($embed->getElementsByTagName('iframe')->item(0)?->getAttribute('src') ?? $embed->getAttribute('src'));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $doc->appendChild($embed);
            $output .= $doc->saveHTML($embed);
            $doc->removeChild($embed);
        }

        return $output;
    }

    public static function hasVideoLinks(?string $html): bool
    {
        if (! $html) {
            return false;
        }

        return (bool) preg_match(self::YOUTUBE_PATTERN, $html) || (bool) preg_match(self::VIMEO_PATTERN, $html);
    }

    private static function replaceAnchors(DOMDocument $doc, DOMNode $root): void
    {
        $anchors = iterator_to_array($doc->getElementsByTagName('a'));

        foreach ($anchors as $anchor) {
            $embed = static::buildEmbedNode($doc, $anchor->getAttribute('href') ?: '');
            if (! $embed) {
                continue;
            }

            $target = $anchor;
            $parent = $anchor->parentNode;

            // If the link is the only thing in its paragraph, swap the whole paragraph out
            if ($parent && $parent->nodeName === 'p'
                && trim($parent->textContent) === trim($anchor->textContent)
                && $parent->childNodes->length === 1) {
                $target = $parent;
                $parent = $parent->parentNode;
            }

            $parent?->replaceChild($embed, $target);
        }
    }

    private static function replaceBareTextLinks(DOMDocument $doc, DOMNode $root): void
    {
        $textNodes = [];
        static::collectTextNodes($root, $textNodes);

        foreach ($textNodes as $textNode) {
            $text = $textNode->textContent;
            $parent = $textNode->parentNode;
            if (! $parent || ! preg_match_all(self::ANY_URL_PATTERN, $text, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            $hits = [];
            foreach ($matches[1] as [$url, $offset]) {
                $embed = static::buildEmbedNode($doc, $url);
                if ($embed) {
                    $hits[] = ['url' => $url, 'offset' => $offset, 'embed' => $embed];
                }
            }

            if (! $hits) {
                continue;
            }

            // Whole text node is a single video link and its paragraph holds nothing else:
            // swap the paragraph itself out so we don't nest a block-level embed inside <p>.
            if (count($hits) === 1
                && trim($text) === trim($hits[0]['url'])
                && $parent->nodeName === 'p'
                && $parent->childNodes->length === 1
                && $parent->parentNode) {
                $parent->parentNode->replaceChild($hits[0]['embed'], $parent);
                continue;
            }

            $fragment = $doc->createDocumentFragment();
            $cursor = 0;
            foreach ($hits as $hit) {
                $before = substr($text, $cursor, $hit['offset'] - $cursor);
                if ($before !== '') {
                    $fragment->appendChild($doc->createTextNode($before));
                }
                $fragment->appendChild($hit['embed']);
                $cursor = $hit['offset'] + strlen($hit['url']);
            }
            $after = substr($text, $cursor);
            if ($after !== '') {
                $fragment->appendChild($doc->createTextNode($after));
            }

            $parent->replaceChild($fragment, $textNode);
        }
    }

    private static function collectTextNodes(DOMNode $node, array &$out): void
    {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $out[] = $child;
            } else {
                static::collectTextNodes($child, $out);
            }
        }
    }

    private static function buildEmbedNode(DOMDocument $doc, string $url): ?DOMElement
    {
        if (preg_match(self::YOUTUBE_PATTERN, $url, $m)) {
            return static::iframeEmbed($doc, "https://www.youtube.com/embed/{$m[1]}");
        }

        if (preg_match(self::VIMEO_PATTERN, $url, $m)) {
            return static::iframeEmbed($doc, "https://player.vimeo.com/video/{$m[1]}");
        }

        if (preg_match(self::DIRECT_VIDEO_PATTERN, $url)) {
            return static::directVideoEmbed($doc, $url);
        }

        return null;
    }

    private static function iframeEmbed(DOMDocument $doc, string $embedUrl): DOMElement
    {
        $wrapper = $doc->createElement('div');
        $wrapper->setAttribute('class', 'video-embed');
        $wrapper->setAttribute('style', 'position:relative;width:100%;aspect-ratio:16/9;margin:2rem 0;overflow:hidden;border-radius:8px;');

        $iframe = $doc->createElement('iframe');
        $iframe->setAttribute('src', $embedUrl);
        $iframe->setAttribute('style', 'position:absolute;inset:0;width:100%;height:100%;border:0;');
        $iframe->setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
        $iframe->setAttribute('allowfullscreen', 'allowfullscreen');
        $iframe->setAttribute('loading', 'lazy');

        $wrapper->appendChild($iframe);

        return $wrapper;
    }

    private static function directVideoEmbed(DOMDocument $doc, string $url): DOMElement
    {
        $video = $doc->createElement('video');
        $video->setAttribute('src', $url);
        $video->setAttribute('controls', 'controls');
        $video->setAttribute('preload', 'metadata');
        $video->setAttribute('style', 'width:100%;border-radius:8px;margin:2rem 0;');

        return $video;
    }
}

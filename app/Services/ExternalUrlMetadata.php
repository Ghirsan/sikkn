<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExternalUrlMetadata
{
    public function suggestedTitle(?string $title): ?string
    {
        if (! $title) {
            return null;
        }

        $suggestedTitle = trim(preg_replace('/(?:\s*[\.\(\[\-]?\s*(?:pdf|avif|gif|jpe?g|png|webp|mp4|m4v|mov|ogv|webm|video|image|gambar)\s*[\)\]]?)$/iu', '', $title));

        return $suggestedTitle !== '' ? $suggestedTitle : trim($title);
    }

    public function fetch(string $url): array
    {
        $parts = parse_url(trim($url));
        $host = $parts['host'] ?? null;

        if (! $host || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
            return $this->fallback($url);
        }

        try {
            $response = Http::accept('text/html')
                ->connectTimeout(3)
                ->timeout(6)
                ->get($url);

            if (! $response->successful() || ! str_contains(strtolower($response->header('Content-Type', '')), 'text/html')) {
                return $this->fallback($url);
            }

            return $this->parse($url, $response->body());
        } catch (\Throwable) {
            return $this->fallback($url);
        }
    }

    private function parse(string $url, string $html): array
    {
        $document = new \DOMDocument;
        libxml_use_internal_errors(true);
        @$document->loadHTML(substr($html, 0, 512000));
        libxml_clear_errors();

        $xpath = new \DOMXPath($document);

        return [
            'title' => $this->firstMeta($xpath, ['og:title', 'twitter:title']) ?: $this->text($xpath->query('//title')->item(0)?->textContent),
            'description' => $this->firstMeta($xpath, ['og:description', 'twitter:description', 'description']),
            'site_name' => $this->firstMeta($xpath, ['og:site_name']) ?: parse_url($url, PHP_URL_HOST),
        ];
    }

    private function firstMeta(\DOMXPath $xpath, array $names): ?string
    {
        foreach ($names as $name) {
            $nodes = $xpath->query(sprintf('//meta[@property="%s"]/@content | //meta[@name="%s"]/@content', $name, $name));
            $value = $this->text($nodes->item(0)?->nodeValue);
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function fallback(string $url): array
    {
        return [
            'title' => null,
            'description' => null,
            'site_name' => parse_url($url, PHP_URL_HOST),
        ];
    }

    private function text(?string $value): ?string
    {
        $value = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode((string) $value))));

        return $value === '' ? null : mb_substr($value, 0, 300);
    }
}

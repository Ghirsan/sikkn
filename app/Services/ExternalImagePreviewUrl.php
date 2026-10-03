<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExternalImagePreviewUrl
{
    public function resolve(string $value): string
    {
        $url = trim($value);
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');

        if (($parts['scheme'] ?? '') !== 'https' || ! $host) {
            throw new \InvalidArgumentException('Gunakan tautan HTTPS dari Google Drive atau imgbb.');
        }

        if ($host === 'drive.google.com') {
            if (! preg_match('#^/file/d/([A-Za-z0-9_-]+)/view$#', $parts['path'] ?? '', $matches)) {
                throw new \InvalidArgumentException('Gunakan tautan Google Drive dengan format /file/d/.../view.');
            }

            return 'https://drive.google.com/thumbnail?id='.$matches[1];
        }

        if (in_array($host, ['ibb.co.com', 'ibb.co'], true)) {
            $response = Http::accept('text/html')->timeout(8)->get($url);

            if (! $response->successful()) {
                throw new \InvalidArgumentException('Tautan imgbb tidak dapat dibuka.');
            }

            $document = new \DOMDocument;
            @$document->loadHTML($response->body());
            $xpath = new \DOMXPath($document);
            $imageNode = $xpath->query('//meta[@property="og:image"]/@content')->item(0);
            $imageUrl = $imageNode?->nodeValue;
            $pageId = trim($parts['path'] ?? '', '/');
            $imagePath = parse_url($imageUrl ?: '', PHP_URL_PATH);
            $imageFilename = $imagePath ? basename($imagePath) : '';

            if (
                ! $imageUrl
                || ! preg_match('#^https://i\.ibb\.co(?:\.com)?/#i', $imageUrl)
                || ! preg_match('/^[A-Za-z0-9]+$/', $pageId)
                || ! preg_match('/^[A-Za-z0-9._-]+$/', $imageFilename)
            ) {
                throw new \InvalidArgumentException('URL gambar imgbb tidak ditemukan pada tautan tersebut.');
            }

            return 'https://i.ibb.co.com/'.$pageId.'/'.$imageFilename;
        }

        if (preg_match('#^i\.ibb\.co(?:\.com)?$#i', $host)) {
            return $url;
        }

        throw new \InvalidArgumentException('Tautan harus berasal dari Google Drive atau imgbb.');
    }
}

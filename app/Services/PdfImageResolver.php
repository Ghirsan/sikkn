<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PdfImageResolver
{
    public function __construct(
        protected ExternalImagePreviewUrl $externalImagePreviewUrl
    ) {}

    /**
     * Resolve an image path or URL into a base64 Data URI suitable for DomPDF.
     */
    public function resolve(?string $path, ?string $resolvedUrl = null): ?string
    {
        if (blank($path)) {
            return null;
        }

        $path = trim($path);

        // 1. Check if it's a local file path
        if (! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://')) {
            return $this->resolveLocalFile($path);
        }

        // 2. External URL
        return $this->resolveExternalUrl($path, $resolvedUrl);
    }

    /**
     * Resolve a local file to a base64 Data URI.
     */
    protected function resolveLocalFile(string $path): ?string
    {
        // Strip common prefixes
        $cleanPath = ltrim(preg_replace('#^(public/|storage/|/storage/)#', '', $path), '/');

        $candidates = [
            storage_path('app/public/'.$cleanPath),
            public_path('storage/'.$cleanPath),
            public_path($cleanPath),
            public_path($path),
            $path, // in case it's an absolute path
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && file_exists($candidate) && is_file($candidate)) {
                $mime = mime_content_type($candidate) ?: 'image/jpeg';
                $content = file_get_contents($candidate);

                return $this->encodeToDataUri($content, $mime);
            }
        }

        return null;
    }

    /**
     * Resolve an external URL to a base64 Data URI with caching.
     */
    protected function resolveExternalUrl(string $path, ?string $resolvedUrl = null): ?string
    {
        $targetUrl = $resolvedUrl ?: $path;

        // Try resolving preview link if it's Google Drive or imgbb
        try {
            $previewUrl = $this->externalImagePreviewUrl->resolve($path);
            if (! empty($previewUrl)) {
                $targetUrl = $previewUrl;
            }
        } catch (\Throwable $e) {
            // Keep targetUrl as-is if external resolver is not applicable (e.g. direct image URL)
        }

        if (empty($targetUrl) || (! str_starts_with($targetUrl, 'http://') && ! str_starts_with($targetUrl, 'https://'))) {
            return null;
        }

        $cacheKey = 'pdf_img_b64_'.md5($targetUrl);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($targetUrl) {
            try {
                $response = Http::timeout(8)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) SIKKN/1.0',
                    'Accept' => 'image/*,*/*;q=0.8',
                ])->get($targetUrl);

                if (! $response->successful() || strlen($response->body()) === 0) {
                    return null;
                }

                $body = $response->body();
                $mime = $response->header('Content-Type');

                if (! $mime || ! str_starts_with($mime, 'image/')) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->buffer($body) ?: 'image/jpeg';
                }

                $mime = explode(';', $mime)[0];

                return $this->encodeToDataUri($body, $mime);
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    /**
     * Convert binary image data to a base64 Data URI, converting WebP to JPEG if necessary.
     */
    protected function encodeToDataUri(string $content, string $mime): ?string
    {
        // DomPDF does not support WebP, convert to JPEG if GD webp is available
        if ($mime === 'image/webp' && function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
            $image = @imagecreatefromstring($content);
            if ($image) {
                ob_start();
                imagejpeg($image, null, 90);
                $content = ob_get_clean();
                imagedestroy($image);
                $mime = 'image/jpeg';
            }
        }

        return 'data:'.$mime.';base64,'.base64_encode($content);
    }
}

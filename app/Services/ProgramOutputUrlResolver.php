<?php

namespace App\Services;

use App\Enums\ProgramOutputType;

class ProgramOutputUrlResolver
{
    private const IMAGE_EXTENSIONS = ['avif', 'gif', 'jpeg', 'jpg', 'png', 'webp'];

    private const VIDEO_EXTENSIONS = ['m4v', 'mov', 'mp4', 'ogv', 'webm'];

    public function infer(string $value, ?string $metadataTitle = null): ProgramOutputType
    {
        $parts = parse_url(trim($value));
        $host = strtolower($parts['host'] ?? '');
        $path = strtolower($parts['path'] ?? '');

        if ($this->isVideoProvider($host)) {
            return ProgramOutputType::Video;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $type = match (true) {
            $extension === 'pdf' => ProgramOutputType::Pdf,
            in_array($extension, self::IMAGE_EXTENSIONS, true) => ProgramOutputType::Image,
            in_array($extension, self::VIDEO_EXTENSIONS, true) => ProgramOutputType::Video,
            default => $this->inferFromTitle($metadataTitle),
        };

        return $type;
    }

    private function inferFromTitle(?string $title): ProgramOutputType
    {
        if (! $title) {
            return ProgramOutputType::Lainnya;
        }

        $title = strtolower(trim($title));
        if (! preg_match('/(?:^|[.\s_\-])((?:pdf)|(?:mp4|m4v|mov|ogv|webm)|(?:avif|gif|jpe?g|png|webp))\s*$/i', $title, $matches)) {
            return ProgramOutputType::Lainnya;
        }

        return match (strtolower($matches[1])) {
            'pdf' => ProgramOutputType::Pdf,
            'mp4', 'm4v', 'mov', 'ogv', 'webm' => ProgramOutputType::Video,
            'avif', 'gif', 'jpg', 'jpeg', 'png', 'webp' => ProgramOutputType::Image,
            default => ProgramOutputType::Lainnya,
        };
    }

    public function embedUrl(string $value, ProgramOutputType $type): string
    {
        $url = trim($value);
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');

        if ($type === ProgramOutputType::Video) {
            if ($host === 'youtu.be') {
                $videoId = trim($parts['path'] ?? '', '/');

                return $videoId ? 'https://www.youtube.com/embed/'.$videoId : $url;
            }

            if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
                $videoId = $this->youtubeVideoId($parts);

                return $videoId ? 'https://www.youtube.com/embed/'.$videoId : $url;
            }

            if (in_array($host, ['vimeo.com', 'www.vimeo.com'], true)) {
                $videoId = trim($parts['path'] ?? '', '/');

                return ctype_digit($videoId) ? 'https://player.vimeo.com/video/'.$videoId : $url;
            }
        }

        if ($host === 'drive.google.com' && preg_match('#^/file/d/([A-Za-z0-9_-]+)/view$#', $parts['path'] ?? '', $matches)) {
            return 'https://drive.google.com/file/d/'.$matches[1].'/preview';
        }

        return $url;
    }

    private function youtubeVideoId(array $parts): ?string
    {
        parse_str($parts['query'] ?? '', $query);

        if (! empty($query['v']) && preg_match('/^[A-Za-z0-9_-]{6,}$/', $query['v'])) {
            return $query['v'];
        }

        if (preg_match('#^/(?:embed|shorts)/([A-Za-z0-9_-]{6,})$#', $parts['path'] ?? '', $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function isVideoProvider(string $host): bool
    {
        return $host === 'youtu.be'
            || in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)
            || in_array($host, ['vimeo.com', 'www.vimeo.com'], true);
    }
}

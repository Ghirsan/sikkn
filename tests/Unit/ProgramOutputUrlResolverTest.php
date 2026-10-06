<?php

namespace Tests\Unit;

use App\Enums\ProgramOutputType;
use App\Services\ProgramOutputUrlResolver;
use PHPUnit\Framework\TestCase;

class ProgramOutputUrlResolverTest extends TestCase
{
    public function test_it_infers_supported_types_from_urls(): void
    {
        $resolver = new ProgramOutputUrlResolver;

        $this->assertSame(ProgramOutputType::Pdf, $resolver->infer('https://example.com/report.pdf?download=1'));
        $this->assertSame(ProgramOutputType::Image, $resolver->infer('https://cdn.example.com/photo.webp'));
        $this->assertSame(ProgramOutputType::Video, $resolver->infer('https://youtu.be/abc12345678'));
        $this->assertSame(ProgramOutputType::Pdf, $resolver->infer('https://example.com/share/report', 'Annual report.pdf'));
        $this->assertSame(ProgramOutputType::Image, $resolver->infer('https://example.com/share/photo', 'Community photo.JPG'));
        $this->assertSame(ProgramOutputType::Video, $resolver->infer('https://example.com/share/clip', 'Workshop recording.webm'));
        $this->assertSame(ProgramOutputType::Lainnya, $resolver->infer('https://example.com/share/report'));
        $this->assertSame(ProgramOutputType::Lainnya, ProgramOutputType::from('lainnya'));
    }
}

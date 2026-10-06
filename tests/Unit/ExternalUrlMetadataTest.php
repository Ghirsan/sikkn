<?php

namespace Tests\Unit;

use App\Services\ExternalUrlMetadata;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalUrlMetadataTest extends TestCase
{
    public function test_it_removes_trailing_file_type_from_suggested_titles(): void
    {
        $metadata = app(ExternalUrlMetadata::class);

        $this->assertSame('Annual report', $metadata->suggestedTitle('Annual report.pdf'));
        $this->assertSame('Community photo', $metadata->suggestedTitle('Community photo (JPG)'));
        $this->assertSame('Workshop recording', $metadata->suggestedTitle('Workshop recording WEBM'));
    }

    public function test_it_extracts_open_graph_metadata(): void
    {
        Http::fake([
            'https://example.com/article' => Http::response(
                '<html><head><meta property="og:title" content="Article title"><meta property="og:description" content="Article summary"><meta property="og:site_name" content="Example"></head></html>',
                200,
                ['Content-Type' => 'text/html'],
            ),
        ]);

        $metadata = app(ExternalUrlMetadata::class)->fetch('https://example.com/article');

        $this->assertSame('Article title', $metadata['title']);
        $this->assertSame('Article summary', $metadata['description']);
        $this->assertSame('Example', $metadata['site_name']);
    }

    public function test_it_falls_back_to_host_for_unavailable_metadata(): void
    {
        Http::fake([
            'https://example.com/file.pdf' => Http::response('', 404),
        ]);

        $metadata = app(ExternalUrlMetadata::class)->fetch('https://example.com/file.pdf');

        $this->assertNull($metadata['title']);
        $this->assertNull($metadata['description']);
        $this->assertSame('example.com', $metadata['site_name']);
    }
}

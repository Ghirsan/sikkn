<?php

namespace Tests\Unit;

use App\Services\PdfImageResolver;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PdfImageResolverTest extends TestCase
{
    public function test_resolves_local_image_to_data_uri(): void
    {
        $resolver = app(PdfImageResolver::class);

        // Test with existing logo
        $logoPath = public_path('images/logo-undip.png');
        $this->assertFileExists($logoPath);

        $dataUri = $resolver->resolve('images/logo-undip.png');

        $this->assertNotNull($dataUri);
        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);
    }

    public function test_resolves_external_image_url_with_http_mock(): void
    {
        $fakeImageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://example.com/test.png' => Http::response($fakeImageData, 200, ['Content-Type' => 'image/png']),
        ]);

        $resolver = app(PdfImageResolver::class);
        $dataUri = $resolver->resolve('https://example.com/test.png');

        $this->assertNotNull($dataUri);
        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);
    }

    public function test_returns_null_for_empty_or_invalid_image(): void
    {
        $resolver = app(PdfImageResolver::class);

        $this->assertNull($resolver->resolve(null));
        $this->assertNull($resolver->resolve(''));
        $this->assertNull($resolver->resolve('non_existent_image_path.jpg'));
    }
}

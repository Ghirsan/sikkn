<?php

namespace App\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PdfKitService
{
    protected string $nodeBinary;

    protected string $scriptDirectory;

    public function __construct(?string $nodeBinary = null, ?string $scriptDirectory = null)
    {
        $this->nodeBinary = $nodeBinary ?? config('services.pdfkit.node_path', 'node');
        $this->scriptDirectory = $scriptDirectory ?? base_path('scripts/pdf');
    }

    /**
     * Generate a PDF by executing a Node.js PDFKit script.
     *
     * @param  string  $scriptName  e.g. 'generate-lrk.js'
     * @param  array<string, mixed>  $payload  Structured data passed to the Node script
     * @param  int  $timeout  Timeout in seconds
     * @return string Raw PDF binary string
     *
     * @throws RuntimeException
     */
    public function generate(string $scriptName, array $payload, int $timeout = 60): string
    {
        $scriptPath = $this->scriptDirectory.DIRECTORY_SEPARATOR.$scriptName;

        if (! File::exists($scriptPath)) {
            throw new RuntimeException("PDFKit runner script not found at: {$scriptPath}");
        }

        $tempDir = storage_path('app/temp/pdf');
        File::ensureDirectoryExists($tempDir);

        $uuid = (string) Str::uuid();
        $tempPayloadFile = $tempDir.DIRECTORY_SEPARATOR.$uuid.'.json';
        $tempOutputFile = $tempDir.DIRECTORY_SEPARATOR.$uuid.'.pdf';

        try {
            File::put($tempPayloadFile, json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

            $process = new Process([
                $this->nodeBinary,
                $scriptPath,
                '--input='.$tempPayloadFile,
                '--output='.$tempOutputFile,
            ]);

            $process->setTimeout($timeout);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            if (! File::exists($tempOutputFile)) {
                $stderr = $process->getErrorOutput();
                throw new RuntimeException('PDFKit script finished but no output file was generated: '.$stderr);
            }

            return File::get($tempOutputFile);
        } finally {
            if (File::exists($tempPayloadFile)) {
                File::delete($tempPayloadFile);
            }
            if (File::exists($tempOutputFile)) {
                File::delete($tempOutputFile);
            }
        }
    }

    /**
     * Return a standardized binary PDF response.
     *
     * @param  string  $pdfBinary  Raw PDF contents
     * @param  string  $filename  Filename presented to user
     * @param  bool  $inline  Whether to display inline in browser or download
     */
    public function makeResponse(string $pdfBinary, string $filename, bool $inline = false): Response
    {
        $disposition = $inline ? 'inline' : 'attachment';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
            'Content-Length' => strlen($pdfBinary),
            'Cache-Control' => 'private, no-transform, no-store, must-revalidate',
        ]);
    }
}

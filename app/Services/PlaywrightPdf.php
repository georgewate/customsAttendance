<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use RuntimeException;

class PlaywrightPdf
{
    /**
     * Render a Blade view to a PDF using headless Chromium via Playwright.
     * Returns the absolute path to the generated PDF file; the caller is
     * responsible for streaming it out and deleting the temporary file.
     *
     * @param  array<string, mixed>  $data
     */
    public function render(string $view, array $data, string $title): string
    {
        $html = view($view, $data)->render();

        $base = sys_get_temp_dir().DIRECTORY_SEPARATOR.'smart-attendance-'.bin2hex(random_bytes(6));
        $htmlPath = $base.'.html';
        $pdfPath = $base.'.pdf';

        file_put_contents($htmlPath, $html);

        try {
            $result = Process::timeout(90)
                ->path(base_path())
                ->env($this->environment())
                ->run([
                    'node',
                    'scripts/pdf-html.mjs',
                    $htmlPath,
                    $pdfPath,
                    $title,
                ]);

            if (! $result->successful()) {
                throw new RuntimeException('Playwright PDF generation failed: '.trim($result->errorOutput()));
            }

            if (! is_file($pdfPath)) {
                throw new RuntimeException('Playwright did not produce a PDF file.');
            }

            return $pdfPath;
        } finally {
            @unlink($htmlPath);
        }
    }

    /**
     * Build the environment for the node child process.
     *
     * On Windows, spawning node with an *inherited* environment (null env) makes
     * Node 22+ abort during startup with "Assertion failed: ncrypto::CSPRNG(...)".
     * Passing an explicit environment block avoids the crash entirely, so we build
     * one from the current process environment and ensure the standard Windows
     * variables are always present. The variables come from getenv(), which in a
     * web-server context omits some console-only entries (e.g. HOME).
     *
     * @return array<string, string>
     */
    protected function environment(): array
    {
        $env = getenv();

        foreach ([
            'SystemRoot' => 'C:\\Windows',
            'ComSpec' => 'C:\\Windows\\System32\\cmd.exe',
            'TEMP' => sys_get_temp_dir(),
            'TMP' => sys_get_temp_dir(),
            'USERPROFILE' => getenv('USERPROFILE') ?: getenv('HOME') ?: '',
            'LOCALAPPDATA' => getenv('LOCALAPPDATA') ?: getenv('USERPROFILE').'\\AppData\\Local',
            'APPDATA' => getenv('APPDATA') ?: getenv('USERPROFILE').'\\AppData\\Roaming',
        ] as $key => $default) {
            $env[$key] = empty($env[$key]) ? $default : $env[$key];
        }

        return $env;
    }
}

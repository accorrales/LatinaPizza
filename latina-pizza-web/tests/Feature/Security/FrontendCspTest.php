<?php

namespace Tests\Feature\Security;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class FrontendCspTest extends TestCase
{
    public function test_blade_views_do_not_contain_executable_script_tags_or_native_inline_handlers(): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(resource_path('views'), RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $violations = [];

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            $relative = str_replace(resource_path('views').DIRECTORY_SEPARATOR, '', $file->getPathname());

            if (preg_match('/<script\b/i', $contents)) {
                $violations[] = $relative.' contains a <script> tag';
            }

            if (preg_match('/\son[a-z]+\s*=/i', $contents)) {
                $violations[] = $relative.' contains a native inline event handler';
            }
        }

        $this->assertSame([], $violations, implode("\n", $violations));
    }

    public function test_web_csp_forbids_inline_handlers_and_does_not_require_eval_or_inline_scripts(): void
    {
        $middleware = file_get_contents(app_path('Http/Middleware/SecurityHeaders.php'));

        preg_match('/"script-src ([^"]+)"/', $middleware, $match);
        $scriptSources = $match[1] ?? '';

        $this->assertNotSame('', $scriptSources, 'script-src directive was not found.');
        $this->assertStringNotContainsString("'unsafe-inline'", $scriptSources);
        $this->assertStringNotContainsString("'unsafe-eval'", $scriptSources);
        $this->assertStringContainsString("script-src-attr 'none'", $middleware);
        $this->assertStringContainsString('https://cdn.jsdelivr.net', $scriptSources);
        $this->assertStringContainsString('https://js.stripe.com', $scriptSources);
    }

    public function test_frontend_bootstrap_uses_the_pinned_alpine_csp_build(): void
    {
        $bootstrap = file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString('@alpinejs/csp@3.17.3', $bootstrap);
        $this->assertStringNotContainsString("from 'alpinejs'", $bootstrap);
    }
}

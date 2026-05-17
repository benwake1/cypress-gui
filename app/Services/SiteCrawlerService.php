<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Services;

use App\DTOs\CrawlResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class SiteCrawlerService
{
    public function crawl(string $url, array $options = []): CrawlResult
    {
        $this->validateUrl($url);

        $timeout = $options['timeout'] ?? 30000;
        $nodePath = env('NODE_PATH', 'node');
        $scriptPath = resource_path('scripts/crawl-page.cjs');

        $result = Process::timeout(60)->run([
            $nodePath,
            $scriptPath,
            $url,
            "--timeout={$timeout}",
        ]);

        if ($result->failed()) {
            Log::error('Site crawl failed', [
                'url' => $url,
                'exit_code' => $result->exitCode(),
                'stderr' => $result->errorOutput(),
            ]);
            throw new \RuntimeException("Failed to crawl {$url}: " . $result->errorOutput());
        }

        $data = json_decode($result->output(), true);

        if (!is_array($data)) {
            throw new \RuntimeException("Crawl returned invalid JSON for {$url}");
        }

        return CrawlResult::fromArray($data);
    }

    private function validateUrl(string $url): void
    {
        $parsed = parse_url($url);

        if (!$parsed || !isset($parsed['scheme'], $parsed['host'])) {
            throw new \InvalidArgumentException('Invalid URL provided');
        }

        if (!in_array($parsed['scheme'], ['http', 'https'], true)) {
            throw new \InvalidArgumentException('URL must use http or https');
        }

        $host = strtolower($parsed['host']);

        $blocked = ['localhost', '127.0.0.1', '0.0.0.0', '::1'];
        if (in_array($host, $blocked, true)) {
            throw new \InvalidArgumentException('Cannot crawl localhost or loopback addresses');
        }

        if (preg_match('/^(10\.|172\.(1[6-9]|2\d|3[01])\.|192\.168\.)/', $host)) {
            throw new \InvalidArgumentException('Cannot crawl private network addresses');
        }
    }
}

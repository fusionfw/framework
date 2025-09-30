<?php

namespace Fusion\Benchmark;

use Fusion\\Logger;

/**
 * Benchmark Runner untuk mengukur performa
 */
class BenchmarkRunner
{
    private $logger;
    private $results = [];

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Run benchmark test
     */
    public function run(string $name, callable $test, int $iterations = 1000): array
    {
        $this->logger->info("Running benchmark: {$name} ({$iterations} iterations)");

        // Warmup
        for ($i = 0; $i < 10; $i++) {
            $test();
        }

        // Actual benchmark
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        for ($i = 0; $i < $iterations; $i++) {
            $test();
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $duration = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;

        $result = [
            'name' => $name,
            'iterations' => $iterations,
            'duration' => $duration,
            'memory_used' => $memoryUsed,
            'rps' => $iterations / $duration,
            'avg_latency' => ($duration * 1000) / $iterations, // ms
            'memory_per_request' => $memoryUsed / $iterations
        ];

        $this->results[] = $result;
        return $result;
    }

    /**
     * Run HTTP benchmark
     */
    public function runHttp(string $url, int $concurrency = 10, int $requests = 100): array
    {
        $this->logger->info("Running HTTP benchmark: {$url} (concurrency: {$concurrency}, requests: {$requests})");

        $startTime = microtime(true);
        $results = [];
        $errors = 0;

        // Simple HTTP benchmark using curl
        $multiHandle = curl_multi_init();
        $curlHandles = [];

        for ($i = 0; $i < $requests; $i++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_multi_add_handle($multiHandle, $ch);
            $curlHandles[] = $ch;
        }

        $running = null;
        do {
            curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle);
        } while ($running > 0);

        foreach ($curlHandles as $ch) {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

            if ($httpCode >= 200 && $httpCode < 300) {
                $results[] = $totalTime;
            } else {
                $errors++;
            }

            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
        }

        curl_multi_close($multiHandle);

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        $result = [
            'name' => 'HTTP Benchmark',
            'url' => $url,
            'concurrency' => $concurrency,
            'total_requests' => $requests,
            'successful_requests' => count($results),
            'errors' => $errors,
            'duration' => $duration,
            'rps' => $requests / $duration,
            'avg_latency' => count($results) > 0 ? array_sum($results) / count($results) * 1000 : 0,
            'min_latency' => count($results) > 0 ? min($results) * 1000 : 0,
            'max_latency' => count($results) > 0 ? max($results) * 1000 : 0
        ];

        $this->results[] = $result;
        return $result;
    }

    /**
     * Get all results
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Generate report
     */
    public function generateReport(): string
    {
        $report = "\n" . str_repeat("=", 80) . "\n";
        $report .= "FLEXIFY FRAMEWORK BENCHMARK REPORT\n";
        $report .= str_repeat("=", 80) . "\n\n";

        foreach ($this->results as $result) {
            $report .= "Test: {$result['name']}\n";
            $report .= str_repeat("-", 40) . "\n";

            if (isset($result['iterations'])) {
                $report .= "Iterations: {$result['iterations']}\n";
                $report .= "Duration: " . number_format($result['duration'], 4) . "s\n";
                $report .= "RPS: " . number_format($result['rps'], 2) . "\n";
                $report .= "Avg Latency: " . number_format($result['avg_latency'], 2) . "ms\n";
                $report .= "Memory Used: " . $this->formatBytes($result['memory_used']) . "\n";
                $report .= "Memory/Request: " . $this->formatBytes($result['memory_per_request']) . "\n";
            } else {
                $report .= "URL: {$result['url']}\n";
                $report .= "Concurrency: {$result['concurrency']}\n";
                $report .= "Total Requests: {$result['total_requests']}\n";
                $report .= "Successful: {$result['successful_requests']}\n";
                $report .= "Errors: {$result['errors']}\n";
                $report .= "Duration: " . number_format($result['duration'], 4) . "s\n";
                $report .= "RPS: " . number_format($result['rps'], 2) . "\n";
                $report .= "Avg Latency: " . number_format($result['avg_latency'], 2) . "ms\n";
                $report .= "Min Latency: " . number_format($result['min_latency'], 2) . "ms\n";
                $report .= "Max Latency: " . number_format($result['max_latency'], 2) . "ms\n";
            }

            $report .= "\n";
        }

        return $report;
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

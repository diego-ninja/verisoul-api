<?php

use Ninja\Verisoul\Support\CircuitBreaker;
use Ninja\Verisoul\Contracts\CacheInterface;
use Ninja\Verisoul\Cache\InMemoryCache;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;

describe('Cache Performance Tests', function () {
    beforeEach(function () {
        // Reset any global state
        gc_collect_cycles();
    });

    describe('In-memory cache performance', function () {
        it('handles high-frequency cache operations efficiently', function () {
            $cache = new InMemoryCache();
            $operations = 10000;
            
            $startTime = microtime(true);
            $startMemory = memory_get_usage(true);

            // Perform mixed cache operations
            for ($i = 1; $i <= $operations; $i++) {
                $key = "performance_key_{$i}";
                $value = [
                    'id' => $i,
                    'data' => str_repeat("cache_data_{$i}", 10),
                    'timestamp' => microtime(true),
                    'metadata' => array_fill(0, 20, "meta_{$i}")
                ];

                // Set operation
                $cache->set($key, $value, 3600);

                // Get operation (every other iteration)
                if ($i % 2 === 0) {
                    $retrieved = $cache->get($key);
                    expect($retrieved)->toBe($value);
                }

                // Delete operation (every 10th iteration)
                if ($i % 10 === 0) {
                    $cache->delete($key);
                }
            }

            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);

            $totalTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
            $memoryUsed = $endMemory - $startMemory;
            $operationsPerSecond = $operations / (($endTime - $startTime));

            expect($totalTime)->toBeLessThan(5000) // Should complete within 5 seconds
                ->and($memoryUsed)->toBeLessThan(50 * 1024 * 1024) // Should use less than 50MB
                ->and($operationsPerSecond)->toBeGreaterThan(1000); // At least 1000 ops/sec
        });

        it('maintains consistent performance under memory pressure', function () {
            $cache = new InMemoryCache();
            $batchSizes = [100, 500, 1000, 2000, 5000];
            $performanceMetrics = [];

            foreach ($batchSizes as $batchSize) {
                $startTime = microtime(true);
                $startMemory = memory_get_usage(true);

                // Fill cache with data
                for ($i = 1; $i <= $batchSize; $i++) {
                    $cache->set(
                        "batch_{$batchSize}_key_{$i}",
                        array_fill(0, 100, "data_chunk_{$i}"),
                        3600
                    );
                }

                // Perform read operations
                $readOperations = min($batchSize, 1000); // Cap reads at 1000
                for ($i = 1; $i <= $readOperations; $i++) {
                    $cache->get("batch_{$batchSize}_key_{$i}");
                }

                $endTime = microtime(true);
                $endMemory = memory_get_usage(true);

                $performanceMetrics[] = [
                    'batch_size' => $batchSize,
                    'time' => ($endTime - $startTime) * 1000,
                    'memory' => $endMemory - $startMemory,
                    'ops_per_sec' => ($batchSize + $readOperations) / ($endTime - $startTime)
                ];

                // Clear cache between batches
                for ($i = 1; $i <= $batchSize; $i++) {
                    $cache->delete("batch_{$batchSize}_key_{$i}");
                }
            }

            // Analyze performance scaling
            expect(count($performanceMetrics))->toBe(count($batchSizes));

            foreach ($performanceMetrics as $metric) {
                expect($metric['time'])->toBeLessThan(10000) // Each batch under 10 seconds
                    ->and($metric['ops_per_sec'])->toBeGreaterThan(100); // At least 100 ops/sec
            }

            // Check that performance doesn't degrade significantly with size
            $firstBatchOpsPerSec = $performanceMetrics[0]['ops_per_sec'];
            $lastBatchOpsPerSec = end($performanceMetrics)['ops_per_sec'];
            $performanceDegradation = ($firstBatchOpsPerSec - $lastBatchOpsPerSec) / $firstBatchOpsPerSec;

            expect($performanceDegradation)->toBeLessThan(0.8); // Less than 80% degradation
        });

        it('efficiently handles cache expiration and cleanup', function () {
            $cache = new InMemoryCache();
            $itemsToCache = 1000;
            $expirationTimes = [1, 2, 3]; // Short expiration times for testing

            $startTime = microtime(true);

            // Add items with different expiration times
            for ($i = 1; $i <= $itemsToCache; $i++) {
                $expiration = $expirationTimes[$i % 3];
                $cache->set(
                    "expiration_test_{$i}",
                    ['data' => "expiring_data_{$i}", 'size' => str_repeat('x', 1000)],
                    $expiration
                );
            }

            $afterCaching = microtime(true);
            $cachingTime = ($afterCaching - $startTime) * 1000;

            // Wait for some items to expire
            sleep(2);

            // Access all items (some should be expired)
            $foundItems = 0;
            $expiredItems = 0;

            $accessStart = microtime(true);
            for ($i = 1; $i <= $itemsToCache; $i++) {
                $result = $cache->get("expiration_test_{$i}");
                if ($result !== null) {
                    $foundItems++;
                } else {
                    $expiredItems++;
                }
            }
            $accessEnd = microtime(true);
            $accessTime = ($accessEnd - $accessStart) * 1000;

            expect($cachingTime)->toBeLessThan(2000) // Caching should be fast
                ->and($accessTime)->toBeLessThan(1000) // Access should be fast
                ->and($expiredItems)->toBeGreaterThan(0) // Some items should have expired
                ->and($foundItems + $expiredItems)->toBe($itemsToCache);
        });
    });

    describe('Circuit breaker cache integration performance', function () {
        it('performs efficiently under circuit breaker load', function () {
            $cache = new InMemoryCache();
            $circuitBreaker = new CircuitBreaker(
                service: 'performance-test',
                cache: $cache,
                failureThreshold: 5,
                recoveryTime: 1000
            );

            $operations = 1000;
            $successfulOperations = 0;
            $failedOperations = 0;

            $startTime = microtime(true);

            for ($i = 1; $i <= $operations; $i++) {
                try {
                    $result = $circuitBreaker->call(function() use ($i) {
                        // 90% success rate
                        if ($i % 10 === 0) {
                            throw new VerisoulConnectionException("Simulated failure #{$i}");
                        }
                        return ['operation' => $i, 'success' => true];
                    });

                    if ($result) {
                        $successfulOperations++;
                    }
                } catch (\Exception $e) {
                    $failedOperations++;
                }
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;
            $operationsPerSecond = $operations / (($endTime - $startTime));

            expect($totalTime)->toBeLessThan(5000) // Should complete within 5 seconds
                ->and($operationsPerSecond)->toBeGreaterThan(200) // At least 200 ops/sec
                ->and($successfulOperations)->toBeGreaterThan(800) // Most should succeed
                ->and($successfulOperations + $failedOperations)->toBe($operations);
        });

        it('handles concurrent circuit breaker instances efficiently', function () {
            $sharedCache = new InMemoryCache();
            $circuitBreakers = [];

            // Create 10 circuit breakers sharing the same cache
            for ($i = 1; $i <= 10; $i++) {
                $circuitBreakers[] = new CircuitBreaker(
                    service: "concurrent-service-{$i}",
                    cache: $sharedCache,
                    failureThreshold: 3,
                    recoveryTime: 500
                );
            }

            $results = [];
            $startTime = microtime(true);

            // Each circuit breaker performs 100 operations concurrently
            $operations = [];
            foreach ($circuitBreakers as $index => $cb) {
                for ($j = 1; $j <= 100; $j++) {
                    $operations[] = function() use ($cb, $index, $j, &$results) {
                        try {
                            $result = $cb->call(function() use ($index, $j) {
                                // Simulate different failure rates per service
                                if ($j % (5 + $index) === 0) {
                                    throw new VerisoulConnectionException("Service {$index} failure");
                                }
                                return ['service' => $index, 'operation' => $j];
                            });
                            $results[] = $result;
                        } catch (\Exception $e) {
                            // Some operations will fail
                        }
                    };
                }
            }

            // Execute all operations
            foreach ($operations as $operation) {
                $operation();
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;
            $totalOperations = count($operations);
            $operationsPerSecond = $totalOperations / (($endTime - $startTime));

            expect(count($results))->toBeGreaterThan(700) // Most operations should succeed
                ->and($totalTime)->toBeLessThan(8000) // Should complete within 8 seconds
                ->and($operationsPerSecond)->toBeGreaterThan(125); // At least 125 ops/sec
        });
    });

    describe('Cache memory optimization', function () {
        it('manages memory efficiently with large cached objects', function () {
            $cache = new InMemoryCache();
            $largeObjectCount = 100;

            $initialMemory = memory_get_usage(true);

            // Cache large objects
            for ($i = 1; $i <= $largeObjectCount; $i++) {
                $largeObject = [
                    'id' => $i,
                    'large_data' => str_repeat("large_cache_data_{$i}", 1000), // ~20KB per object
                    'metadata' => array_fill(0, 200, "metadata_{$i}"),
                    'nested' => [
                        'level1' => array_fill(0, 100, "nested_data_{$i}"),
                        'level2' => [
                            'deep_data' => str_repeat("deep_{$i}", 500)
                        ]
                    ]
                ];

                $cache->set("large_object_{$i}", $largeObject, 3600);
            }

            $afterCaching = memory_get_usage(true);
            $cachingMemory = $afterCaching - $initialMemory;

            // Retrieve all objects
            $retrievalStart = microtime(true);
            $retrievedObjects = [];

            for ($i = 1; $i <= $largeObjectCount; $i++) {
                $retrieved = $cache->get("large_object_{$i}");
                if ($retrieved !== null) {
                    $retrievedObjects[] = $retrieved;
                }
            }

            $retrievalEnd = microtime(true);
            $retrievalTime = ($retrievalEnd - $retrievalStart) * 1000;

            // Clean up half the objects
            for ($i = 1; $i <= $largeObjectCount / 2; $i++) {
                $cache->delete("large_object_{$i}");
            }

            gc_collect_cycles();
            $afterCleanup = memory_get_usage(true);
            $memoryRecovered = $afterCaching - $afterCleanup;

            expect(count($retrievedObjects))->toBe($largeObjectCount)
                ->and($retrievalTime)->toBeLessThan(1000) // Retrieval under 1 second
                ->and($cachingMemory)->toBeLessThan(50 * 1024 * 1024) // Caching under 50MB
                ->and($memoryRecovered)->toBeGreaterThan($cachingMemory * 0.3); // At least 30% memory recovered
        });

        it('prevents memory leaks in long-running cache operations', function () {
            $cache = new InMemoryCache();
            $iterations = 50;
            $memoryReadings = [];

            for ($iteration = 1; $iteration <= $iterations; $iteration++) {
                $iterationStart = memory_get_usage(true);

                // Perform cache operations for this iteration
                for ($i = 1; $i <= 100; $i++) {
                    $key = "iteration_{$iteration}_item_{$i}";
                    $value = [
                        'iteration' => $iteration,
                        'item' => $i,
                        'data' => str_repeat("iter_data_{$iteration}_{$i}", 50)
                    ];

                    $cache->set($key, $value, 60);
                    
                    // Read back immediately
                    $retrieved = $cache->get($key);
                    expect($retrieved)->toBe($value);
                }

                // Clean up this iteration's data
                for ($i = 1; $i <= 100; $i++) {
                    $cache->delete("iteration_{$iteration}_item_{$i}");
                }

                gc_collect_cycles();
                $iterationEnd = memory_get_usage(true);
                $memoryReadings[] = $iterationEnd - $iterationStart;
            }

            // Analyze memory usage pattern
            $avgMemoryPerIteration = array_sum($memoryReadings) / count($memoryReadings);
            $maxMemoryIteration = max($memoryReadings);
            $minMemoryIteration = min($memoryReadings);

            // Check for memory leaks (consistent memory usage across iterations)
            $memoryVariation = ($maxMemoryIteration - $minMemoryIteration);

            expect(count($memoryReadings))->toBe($iterations)
                ->and($avgMemoryPerIteration)->toBeLessThan(2 * 1024 * 1024) // Average under 2MB per iteration
                ->and($memoryVariation)->toBeLessThan(5 * 1024 * 1024); // Variation under 5MB (indicates no major leaks)
        });
    });

    describe('Cache performance under stress', function () {
        it('maintains performance under high concurrency simulation', function () {
            $cache = new InMemoryCache();
            $concurrentOperations = 2000;
            $results = [];

            $startTime = microtime(true);

            // Simulate concurrent operations
            $operations = [];
            for ($i = 1; $i <= $concurrentOperations; $i++) {
                $operations[] = function() use ($cache, $i, &$results) {
                    $operationStart = microtime(true);

                    // Mix of operations
                    switch ($i % 4) {
                        case 0: // Write operation
                            $cache->set("concurrent_key_{$i}", ['data' => "concurrent_data_{$i}"], 300);
                            break;
                        case 1: // Read operation
                            $result = $cache->get("concurrent_key_" . max(1, $i - 10));
                            break;
                        case 2: // Update operation
                            $cache->set("concurrent_key_" . max(1, $i - 5), ['updated' => "updated_data_{$i}"], 300);
                            break;
                        case 3: // Delete operation
                            $cache->delete("concurrent_key_" . max(1, $i - 20));
                            break;
                    }

                    $operationEnd = microtime(true);
                    $results[] = ($operationEnd - $operationStart) * 1000;
                };
            }

            // Execute all operations
            foreach ($operations as $operation) {
                $operation();
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;
            $avgOperationTime = array_sum($results) / count($results);
            $maxOperationTime = max($results);
            $operationsPerSecond = $concurrentOperations / (($endTime - $startTime));

            expect(count($results))->toBe($concurrentOperations)
                ->and($totalTime)->toBeLessThan(10000) // Complete within 10 seconds
                ->and($avgOperationTime)->toBeLessThan(5) // Average operation under 5ms
                ->and($maxOperationTime)->toBeLessThan(100) // Max operation under 100ms
                ->and($operationsPerSecond)->toBeGreaterThan(200); // At least 200 ops/sec
        });

        it('handles cache pressure and eviction efficiently', function () {
            $cache = new InMemoryCache();
            $itemsToCache = 5000; // Large number to trigger potential eviction

            $cachingTimes = [];
            $memoryUsages = [];

            // Fill cache with increasing load
            for ($batch = 1; $batch <= 10; $batch++) {
                $batchStart = microtime(true);
                $batchMemoryStart = memory_get_usage(true);

                $itemsInBatch = $itemsToCache / 10;
                
                for ($i = 1; $i <= $itemsInBatch; $i++) {
                    $itemIndex = (($batch - 1) * $itemsInBatch) + $i;
                    $cache->set(
                        "pressure_test_{$itemIndex}",
                        [
                            'batch' => $batch,
                            'item' => $i,
                            'data' => str_repeat("pressure_data_{$itemIndex}", 100)
                        ],
                        3600
                    );
                }

                $batchEnd = microtime(true);
                $batchMemoryEnd = memory_get_usage(true);

                $cachingTimes[] = ($batchEnd - $batchStart) * 1000;
                $memoryUsages[] = $batchMemoryEnd - $batchMemoryStart;
            }

            // Test retrieval performance under pressure
            $retrievalStart = microtime(true);
            $foundItems = 0;

            for ($i = 1; $i <= $itemsToCache; $i += 10) { // Sample every 10th item
                $result = $cache->get("pressure_test_{$i}");
                if ($result !== null) {
                    $foundItems++;
                }
            }

            $retrievalEnd = microtime(true);
            $retrievalTime = ($retrievalEnd - $retrievalStart) * 1000;

            $avgCachingTimePerBatch = array_sum($cachingTimes) / count($cachingTimes);
            $totalMemoryUsed = array_sum($memoryUsages);

            expect(count($cachingTimes))->toBe(10)
                ->and($avgCachingTimePerBatch)->toBeLessThan(2000) // Average batch under 2 seconds
                ->and($retrievalTime)->toBeLessThan(500) // Retrieval under 500ms
                ->and($totalMemoryUsed)->toBeLessThan(100 * 1024 * 1024) // Total memory under 100MB
                ->and($foundItems)->toBeGreaterThan(0); // Should find some items
        });
    });
});
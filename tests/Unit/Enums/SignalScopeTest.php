<?php

use Ninja\Verisoul\Enums\SignalScope;

describe('SignalScope Enum', function (): void {
    describe('enum cases', function (): void {
        it('has all expected cases', function (): void {
            $cases = SignalScope::cases();
            $values = array_map(fn($case) => $case->value, $cases);

            expect($cases)->toHaveCount(5)
                ->and($values)->toContain('device_network')
                ->and($values)->toContain('document')
                ->and($values)->toContain('session')
                ->and($values)->toContain('referring_session')
                ->and($values)->toContain('account');
        });

        it('has correct enum values', function (): void {
            expect(SignalScope::DeviceNetwork->value)->toBe('device_network')
                ->and(SignalScope::Document->value)->toBe('document')
                ->and(SignalScope::Session->value)->toBe('session');
        });

        it('can be created from string values', function (): void {
            expect(SignalScope::from('device_network'))->toBe(SignalScope::DeviceNetwork)
                ->and(SignalScope::from('document'))->toBe(SignalScope::Document)
                ->and(SignalScope::from('session'))->toBe(SignalScope::Session);
        });

        it('can try to create from string values', function (): void {
            expect(SignalScope::tryFrom('device_network'))->toBe(SignalScope::DeviceNetwork)
                ->and(SignalScope::tryFrom('document'))->toBe(SignalScope::Document)
                ->and(SignalScope::tryFrom('session'))->toBe(SignalScope::Session)
                ->and(SignalScope::tryFrom('invalid'))->toBeNull();
        });
    });

    describe('enum behavior', function (): void {
        it('supports comparison operations', function (): void {
            expect(SignalScope::DeviceNetwork === SignalScope::DeviceNetwork)->toBeTrue()
                ->and(SignalScope::DeviceNetwork === SignalScope::Document)->toBeFalse()
                ->and(SignalScope::Document !== SignalScope::Session)->toBeTrue();
        });

        it('can be used in match expressions', function (): void {
            $scope = SignalScope::Document;

            $category = match ($scope) {
                SignalScope::DeviceNetwork => 'Device and Network Signals',
                SignalScope::Document => 'Document Verification Signals',
                SignalScope::Session => 'Session Analysis Signals',
            };

            expect($category)->toBe('Document Verification Signals');
        });

        it('can be used in conditional logic', function (): void {
            $scope = SignalScope::Session;
            $isDeviceNetwork = SignalScope::DeviceNetwork === $scope;
            $isDocument = SignalScope::Document === $scope;
            $isSession = SignalScope::Session === $scope;

            expect($isDeviceNetwork)->toBeFalse()
                ->and($isDocument)->toBeFalse()
                ->and($isSession)->toBeTrue();
        });

        it('can be used in arrays', function (): void {
            $scopes = [SignalScope::DeviceNetwork, SignalScope::Document, SignalScope::Session];

            expect($scopes)->toHaveCount(3)
                ->and(in_array(SignalScope::DeviceNetwork, $scopes))->toBeTrue()
                ->and(in_array(SignalScope::Document, $scopes))->toBeTrue()
                ->and(in_array(SignalScope::Session, $scopes))->toBeTrue();
        });

        it('supports serialization', function (): void {
            $scope = SignalScope::DeviceNetwork;
            $serialized = serialize($scope);
            $unserialized = unserialize($serialized);

            expect($unserialized)->toBe(SignalScope::DeviceNetwork)
                ->and($unserialized->value)->toBe('device_network');
        });
    });

    describe('signal scope categories', function (): void {
        it('categorizes device and network related signals', function (): void {
            $deviceNetworkScope = SignalScope::DeviceNetwork;

            expect($deviceNetworkScope->value)->toBe('device_network');

            // Test scope identification
            $isDeviceNetworkScope = SignalScope::DeviceNetwork === $deviceNetworkScope;
            expect($isDeviceNetworkScope)->toBeTrue();
        });

        it('categorizes document verification signals', function (): void {
            $documentScope = SignalScope::Document;

            expect($documentScope->value)->toBe('document');

            // Test scope identification
            $isDocumentScope = SignalScope::Document === $documentScope;
            expect($isDocumentScope)->toBeTrue();
        });

        it('categorizes session analysis signals', function (): void {
            $sessionScope = SignalScope::Session;

            expect($sessionScope->value)->toBe('session');

            // Test scope identification
            $isSessionScope = SignalScope::Session === $sessionScope;
            expect($isSessionScope)->toBeTrue();
        });
    });

    describe('practical usage scenarios', function (): void {
        it('enables signal filtering by scope', function (): void {
            $allScopes = SignalScope::cases();
            $deviceNetworkSignals = [];
            $documentSignals = [];
            $sessionSignals = [];

            foreach ($allScopes as $scope) {
                switch ($scope) {
                    case SignalScope::DeviceNetwork:
                        $deviceNetworkSignals[] = $scope;
                        break;
                    case SignalScope::Document:
                        $documentSignals[] = $scope;
                        break;
                    case SignalScope::Session:
                        $sessionSignals[] = $scope;
                        break;
                }
            }

            expect(count($deviceNetworkSignals))->toBe(1)
                ->and(count($documentSignals))->toBe(1)
                ->and(count($sessionSignals))->toBe(1)
                ->and($deviceNetworkSignals[0])->toBe(SignalScope::DeviceNetwork)
                ->and($documentSignals[0])->toBe(SignalScope::Document)
                ->and($sessionSignals[0])->toBe(SignalScope::Session);
        });

        it('supports scope-based configuration', function (): void {
            $scopeConfigurations = [
                'device_network' => [
                    'enabled' => true,
                    'weight' => 0.3,
                    'signals' => ['proxy', 'vpn', 'tor', 'datacenter'],
                ],
                'document' => [
                    'enabled' => true,
                    'weight' => 0.4,
                    'signals' => ['authenticity', 'tampering', 'quality'],
                ],
                'session' => [
                    'enabled' => true,
                    'weight' => 0.3,
                    'signals' => ['behavior', 'timing', 'patterns'],
                ],
            ];

            foreach ($scopeConfigurations as $scopeValue => $config) {
                $scope = SignalScope::from($scopeValue);
                expect($scope)->toBeInstanceOf(SignalScope::class)
                    ->and($config['enabled'])->toBeTrue()
                    ->and($config['weight'])->toBeFloat()
                    ->and($config['signals'])->toBeArray();
            }

            // Verify total weight sums to 1.0
            $totalWeight = array_sum(array_column($scopeConfigurations, 'weight'));
            expect($totalWeight)->toBe(1.0);
        });

        it('enables scope-specific processing logic', function (): void {
            $testScopes = [SignalScope::DeviceNetwork, SignalScope::Document, SignalScope::Session];
            $processingResults = [];

            foreach ($testScopes as $scope) {
                $result = match ($scope) {
                    SignalScope::DeviceNetwork => [
                        'processor' => 'NetworkAnalyzer',
                        'priority' => 'high',
                        'timeout' => 5000,
                    ],
                    SignalScope::Document => [
                        'processor' => 'DocumentVerifier',
                        'priority' => 'critical',
                        'timeout' => 10000,
                    ],
                    SignalScope::Session => [
                        'processor' => 'BehaviorAnalyzer',
                        'priority' => 'medium',
                        'timeout' => 3000,
                    ],
                };

                $processingResults[$scope->value] = $result;
            }

            expect(count($processingResults))->toBe(3)
                ->and($processingResults['device_network']['processor'])->toBe('NetworkAnalyzer')
                ->and($processingResults['document']['processor'])->toBe('DocumentVerifier')
                ->and($processingResults['session']['processor'])->toBe('BehaviorAnalyzer');
        });
    });

    describe('validation and error handling', function (): void {
        it('throws exception for invalid string values', function (): void {
            expect(fn() => SignalScope::from('invalid_scope'))
                ->toThrow(ValueError::class);
        });

        it('handles case sensitivity correctly', function (): void {
            expect(SignalScope::tryFrom('DEVICE_NETWORK'))->toBeNull()
                ->and(SignalScope::tryFrom('Device_Network'))->toBeNull()
                ->and(SignalScope::tryFrom('DOCUMENT'))->toBeNull()
                ->and(SignalScope::tryFrom('Document'))->toBeNull()
                ->and(SignalScope::tryFrom('SESSION'))->toBeNull()
                ->and(SignalScope::tryFrom('Session'))->toBeNull();
        });

        it('handles invalid input types gracefully', function (): void {
            $invalidInputs = ['123', 'invalid_scope', 'random_string'];

            foreach ($invalidInputs as $input) {
                expect(SignalScope::tryFrom($input))->toBeNull();
            }
        });
    });

    describe('string representation', function (): void {
        it('converts to string correctly', function (): void {
            expect(SignalScope::DeviceNetwork->value)->toBe('device_network')
                ->and(SignalScope::Document->value)->toBe('document')
                ->and(SignalScope::Session->value)->toBe('session');
        });

        it('provides meaningful string representation', function (): void {
            expect((string) SignalScope::DeviceNetwork->value)->toBe('device_network')
                ->and((string) SignalScope::Document->value)->toBe('document')
                ->and((string) SignalScope::Session->value)->toBe('session');
        });
    });

    describe('integration patterns', function (): void {
        it('supports risk assessment workflows', function (): void {
            $riskAssessmentFlow = function (SignalScope $scope) {
                return match ($scope) {
                    SignalScope::DeviceNetwork => [
                        'stage' => 'network_analysis',
                        'next' => SignalScope::Session,
                        'fallback' => SignalScope::Document,
                    ],
                    SignalScope::Session => [
                        'stage' => 'behavior_analysis',
                        'next' => SignalScope::Document,
                        'fallback' => SignalScope::DeviceNetwork,
                    ],
                    SignalScope::Document => [
                        'stage' => 'document_verification',
                        'next' => null,
                        'fallback' => SignalScope::Session,
                    ],
                };
            };

            $deviceNetworkFlow = $riskAssessmentFlow(SignalScope::DeviceNetwork);
            $sessionFlow = $riskAssessmentFlow(SignalScope::Session);
            $documentFlow = $riskAssessmentFlow(SignalScope::Document);

            expect($deviceNetworkFlow['stage'])->toBe('network_analysis')
                ->and($deviceNetworkFlow['next'])->toBe(SignalScope::Session)
                ->and($sessionFlow['stage'])->toBe('behavior_analysis')
                ->and($sessionFlow['next'])->toBe(SignalScope::Document)
                ->and($documentFlow['stage'])->toBe('document_verification')
                ->and($documentFlow['next'])->toBeNull();
        });

        it('enables hierarchical signal processing', function (): void {
            $scopeHierarchy = [
                'device_network' => ['level' => 1, 'dependencies' => []],
                'session' => ['level' => 2, 'dependencies' => ['device_network']],
                'document' => ['level' => 3, 'dependencies' => ['device_network', 'session']],
            ];

            foreach ($scopeHierarchy as $scopeValue => $config) {
                $scope = SignalScope::from($scopeValue);
                expect($scope)->toBeInstanceOf(SignalScope::class)
                    ->and($config['level'])->toBeInt()
                    ->and($config['dependencies'])->toBeArray();
            }

            // Test dependency validation
            $documentDeps = $scopeHierarchy['document']['dependencies'];
            expect(in_array('device_network', $documentDeps))->toBeTrue()
                ->and(in_array('session', $documentDeps))->toBeTrue();
        });
    });

    describe('performance and memory efficiency', function (): void {
        it('maintains consistent memory usage across operations', function (): void {
            $initialMemory = memory_get_usage();

            // Perform many scope operations
            for ($i = 0; $i < 1000; $i++) {
                $scope = SignalScope::cases()[$i % 3];
                $value = $scope->value;
                $fromValue = SignalScope::from($value);
                $tryFromValue = SignalScope::tryFrom($value);
            }

            $finalMemory = memory_get_usage();
            $memoryIncrease = $finalMemory - $initialMemory;

            // Memory increase should be minimal
            expect($memoryIncrease)->toBeLessThan(1024 * 1024); // Less than 1MB
        });

        it('performs efficiently in bulk operations', function (): void {
            $startTime = microtime(true);

            // Perform bulk scope operations
            $results = [];
            for ($i = 0; $i < 10000; $i++) {
                $scopes = SignalScope::cases();
                foreach ($scopes as $scope) {
                    $results[] = $scope->value;
                }
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            $expectedCount = 10000 * count(SignalScope::cases());
            expect(count($results))->toBe($expectedCount) // 10000 iterations * number of scopes
                ->and($executionTime)->toBeLessThan(1.0); // Less than 1 second
        });
    });
});

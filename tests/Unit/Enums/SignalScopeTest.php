<?php

use Ninja\Verisoul\Enums\SignalScope;

describe('SignalScope Enum', function () {
    describe('enum cases', function () {
        it('has all expected cases', function () {
            $cases = SignalScope::cases();
            $values = array_map(fn($case) => $case->value, $cases);

            expect($cases)->toHaveCount(3)
                ->and($values)->toContain('device_network')
                ->and($values)->toContain('document')
                ->and($values)->toContain('session');
        });

        it('has correct enum values', function () {
            expect(SignalScope::DeviceNetwork->value)->toBe('device_network')
                ->and(SignalScope::Document->value)->toBe('document')
                ->and(SignalScope::Session->value)->toBe('session');
        });

        it('can be created from string values', function () {
            expect(SignalScope::from('device_network'))->toBe(SignalScope::DeviceNetwork)
                ->and(SignalScope::from('document'))->toBe(SignalScope::Document)
                ->and(SignalScope::from('session'))->toBe(SignalScope::Session);
        });

        it('can try to create from string values', function () {
            expect(SignalScope::tryFrom('device_network'))->toBe(SignalScope::DeviceNetwork)
                ->and(SignalScope::tryFrom('document'))->toBe(SignalScope::Document)
                ->and(SignalScope::tryFrom('session'))->toBe(SignalScope::Session)
                ->and(SignalScope::tryFrom('invalid'))->toBeNull();
        });
    });

    describe('enum behavior', function () {
        it('supports comparison operations', function () {
            expect(SignalScope::DeviceNetwork === SignalScope::DeviceNetwork)->toBeTrue()
                ->and(SignalScope::DeviceNetwork === SignalScope::Document)->toBeFalse()
                ->and(SignalScope::Document !== SignalScope::Session)->toBeTrue();
        });

        it('can be used in match expressions', function () {
            $scope = SignalScope::Document;
            
            $category = match ($scope) {
                SignalScope::DeviceNetwork => 'Device and Network Signals',
                SignalScope::Document => 'Document Verification Signals',
                SignalScope::Session => 'Session Analysis Signals',
            };

            expect($category)->toBe('Document Verification Signals');
        });

        it('can be used in conditional logic', function () {
            $scope = SignalScope::Session;
            $isDeviceNetwork = $scope === SignalScope::DeviceNetwork;
            $isDocument = $scope === SignalScope::Document;
            $isSession = $scope === SignalScope::Session;

            expect($isDeviceNetwork)->toBeFalse()
                ->and($isDocument)->toBeFalse()
                ->and($isSession)->toBeTrue();
        });

        it('can be used in arrays', function () {
            $scopes = [SignalScope::DeviceNetwork, SignalScope::Document, SignalScope::Session];

            expect($scopes)->toHaveCount(3)
                ->and(in_array(SignalScope::DeviceNetwork, $scopes))->toBeTrue()
                ->and(in_array(SignalScope::Document, $scopes))->toBeTrue()
                ->and(in_array(SignalScope::Session, $scopes))->toBeTrue();
        });

        it('supports serialization', function () {
            $scope = SignalScope::DeviceNetwork;
            $serialized = serialize($scope);
            $unserialized = unserialize($serialized);

            expect($unserialized)->toBe(SignalScope::DeviceNetwork)
                ->and($unserialized->value)->toBe('device_network');
        });
    });

    describe('signal scope categories', function () {
        it('categorizes device and network related signals', function () {
            $deviceNetworkScope = SignalScope::DeviceNetwork;
            
            expect($deviceNetworkScope->value)->toBe('device_network');
            
            // Test scope identification
            $isDeviceNetworkScope = $deviceNetworkScope === SignalScope::DeviceNetwork;
            expect($isDeviceNetworkScope)->toBeTrue();
        });

        it('categorizes document verification signals', function () {
            $documentScope = SignalScope::Document;
            
            expect($documentScope->value)->toBe('document');
            
            // Test scope identification
            $isDocumentScope = $documentScope === SignalScope::Document;
            expect($isDocumentScope)->toBeTrue();
        });

        it('categorizes session analysis signals', function () {
            $sessionScope = SignalScope::Session;
            
            expect($sessionScope->value)->toBe('session');
            
            // Test scope identification
            $isSessionScope = $sessionScope === SignalScope::Session;
            expect($isSessionScope)->toBeTrue();
        });
    });

    describe('practical usage scenarios', function () {
        it('enables signal filtering by scope', function () {
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

        it('supports scope-based configuration', function () {
            $scopeConfigurations = [
                SignalScope::DeviceNetwork => [
                    'enabled' => true,
                    'weight' => 0.3,
                    'signals' => ['proxy', 'vpn', 'tor', 'datacenter']
                ],
                SignalScope::Document => [
                    'enabled' => true,
                    'weight' => 0.4,
                    'signals' => ['authenticity', 'tampering', 'quality']
                ],
                SignalScope::Session => [
                    'enabled' => true,
                    'weight' => 0.3,
                    'signals' => ['behavior', 'timing', 'patterns']
                ]
            ];

            foreach ($scopeConfigurations as $scope => $config) {
                expect($scope)->toBeInstanceOf(SignalScope::class)
                    ->and($config['enabled'])->toBeTrue()
                    ->and($config['weight'])->toBeFloat()
                    ->and($config['signals'])->toBeArray();
            }

            // Verify total weight sums to 1.0
            $totalWeight = array_sum(array_column($scopeConfigurations, 'weight'));
            expect($totalWeight)->toBe(1.0);
        });

        it('enables scope-specific processing logic', function () {
            $testScopes = [SignalScope::DeviceNetwork, SignalScope::Document, SignalScope::Session];
            $processingResults = [];

            foreach ($testScopes as $scope) {
                $result = match ($scope) {
                    SignalScope::DeviceNetwork => [
                        'processor' => 'NetworkAnalyzer',
                        'priority' => 'high',
                        'timeout' => 5000
                    ],
                    SignalScope::Document => [
                        'processor' => 'DocumentVerifier',
                        'priority' => 'critical',
                        'timeout' => 10000
                    ],
                    SignalScope::Session => [
                        'processor' => 'BehaviorAnalyzer',
                        'priority' => 'medium',
                        'timeout' => 3000
                    ]
                };
                
                $processingResults[$scope->value] = $result;
            }

            expect(count($processingResults))->toBe(3)
                ->and($processingResults['device_network']['processor'])->toBe('NetworkAnalyzer')
                ->and($processingResults['document']['processor'])->toBe('DocumentVerifier')
                ->and($processingResults['session']['processor'])->toBe('BehaviorAnalyzer');
        });
    });

    describe('validation and error handling', function () {
        it('throws exception for invalid string values', function () {
            expect(fn() => SignalScope::from('invalid_scope'))
                ->toThrow(ValueError::class);
        });

        it('handles case sensitivity correctly', function () {
            expect(SignalScope::tryFrom('DEVICE_NETWORK'))->toBeNull()
                ->and(SignalScope::tryFrom('Device_Network'))->toBeNull()
                ->and(SignalScope::tryFrom('DOCUMENT'))->toBeNull()
                ->and(SignalScope::tryFrom('Document'))->toBeNull()
                ->and(SignalScope::tryFrom('SESSION'))->toBeNull()
                ->and(SignalScope::tryFrom('Session'))->toBeNull();
        });

        it('handles invalid input types gracefully', function () {
            $invalidInputs = [123, [], null, true, false];

            foreach ($invalidInputs as $input) {
                expect(SignalScope::tryFrom($input))->toBeNull();
            }
        });
    });

    describe('string representation', function () {
        it('converts to string correctly', function () {
            expect(SignalScope::DeviceNetwork->value)->toBe('device_network')
                ->and(SignalScope::Document->value)->toBe('document')
                ->and(SignalScope::Session->value)->toBe('session');
        });

        it('provides meaningful string representation', function () {
            expect((string) SignalScope::DeviceNetwork->value)->toBe('device_network')
                ->and((string) SignalScope::Document->value)->toBe('document')
                ->and((string) SignalScope::Session->value)->toBe('session');
        });
    });

    describe('integration patterns', function () {
        it('supports risk assessment workflows', function () {
            $riskAssessmentFlow = function(SignalScope $scope) {
                return match ($scope) {
                    SignalScope::DeviceNetwork => [
                        'stage' => 'network_analysis',
                        'next' => SignalScope::Session,
                        'fallback' => SignalScope::Document
                    ],
                    SignalScope::Session => [
                        'stage' => 'behavior_analysis',
                        'next' => SignalScope::Document,
                        'fallback' => SignalScope::DeviceNetwork
                    ],
                    SignalScope::Document => [
                        'stage' => 'document_verification',
                        'next' => null,
                        'fallback' => SignalScope::Session
                    ]
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

        it('enables hierarchical signal processing', function () {
            $scopeHierarchy = [
                SignalScope::DeviceNetwork => ['level' => 1, 'dependencies' => []],
                SignalScope::Session => ['level' => 2, 'dependencies' => [SignalScope::DeviceNetwork]],
                SignalScope::Document => ['level' => 3, 'dependencies' => [SignalScope::DeviceNetwork, SignalScope::Session]]
            ];

            foreach ($scopeHierarchy as $scope => $config) {
                expect($scope)->toBeInstanceOf(SignalScope::class)
                    ->and($config['level'])->toBeInt()
                    ->and($config['dependencies'])->toBeArray();
            }

            // Test dependency validation
            $documentDeps = $scopeHierarchy[SignalScope::Document]['dependencies'];
            expect(in_array(SignalScope::DeviceNetwork, $documentDeps))->toBeTrue()
                ->and(in_array(SignalScope::Session, $documentDeps))->toBeTrue();
        });
    });

    describe('performance and memory efficiency', function () {
        it('maintains consistent memory usage across operations', function () {
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

        it('performs efficiently in bulk operations', function () {
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
            
            expect(count($results))->toBe(30000) // 10000 iterations * 3 scopes
                ->and($executionTime)->toBeLessThan(1.0); // Less than 1 second
        });
    });
});
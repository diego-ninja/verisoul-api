<?php

use Ninja\Verisoul\DTO\RiskSignal;
use Ninja\Verisoul\Enums\SignalScope;
use Ninja\Verisoul\ValueObjects\Score;

describe('RiskSignal DTO', function (): void {
    describe('construction', function (): void {
        it('can be created with all properties', function (): void {
            $score = new Score(0.85);
            $riskSignal = new RiskSignal(
                name: 'device_risk',
                score: $score,
                scope: SignalScope::DeviceNetwork,
            );

            expect($riskSignal->name)->toBe('device_risk')
                ->and($riskSignal->score)->toBe($score)
                ->and($riskSignal->scope)->toBe(SignalScope::DeviceNetwork);
        });

        it('can be created with default scope', function (): void {
            $score = new Score(0.65);
            $riskSignal = new RiskSignal(
                name: 'proxy',
                score: $score,
            );

            expect($riskSignal->name)->toBe('proxy')
                ->and($riskSignal->score)->toBe($score)
                ->and($riskSignal->scope)->toBe(SignalScope::DeviceNetwork);
        });

        it('can be created with different scopes', function (): void {
            $documentSignal = new RiskSignal(
                name: 'id_validity',
                score: new Score(0.95),
                scope: SignalScope::Document,
            );

            $sessionSignal = new RiskSignal(
                name: 'impossible_travel',
                score: new Score(0.75),
                scope: SignalScope::ReferringSession,
            );

            expect($documentSignal->scope)->toBe(SignalScope::Document)
                ->and($sessionSignal->scope)->toBe(SignalScope::ReferringSession);
        });
    });

    describe('immutability', function (): void {
        it('is readonly and immutable', function (): void {
            $riskSignal = new RiskSignal(
                name: 'test_signal',
                score: new Score(0.5),
                scope: SignalScope::DeviceNetwork,
            );

            $reflection = new ReflectionClass($riskSignal);
            $properties = $reflection->getProperties();

            foreach ($properties as $property) {
                expect($property->isReadOnly())->toBeTrue(
                    "Property {$property->getName()} should be readonly",
                );
            }
        });

        it('maintains data integrity', function (): void {
            $score = new Score(0.42);
            $riskSignal = new RiskSignal(
                name: 'vpn',
                score: $score,
                scope: SignalScope::DeviceNetwork,
            );

            expect($riskSignal->name)->toBe('vpn');
            expect($riskSignal->name)->toBe('vpn'); // Second call
            expect($riskSignal->score)->toBe($score);
            expect($riskSignal->score)->toBe($score); // Second call
        });
    });

    describe('display name functionality', function (): void {
        it('returns correct display names for device network signals', function (): void {
            $deviceSignals = [
                'device_risk' => 'Device Risk',
                'proxy' => 'Proxy',
                'vpn' => 'VPN',
                'datacenter' => 'Datacenter',
                'tor' => 'Tor',
                'spoofed_ip' => 'Spoofed IP',
                'recent_fraud_ip' => 'Recent Fraud IP',
                'device_network_mismatch' => 'Device Network Mismatch',
                'location_spoofing' => 'Location Spoofing',
            ];

            foreach ($deviceSignals as $signalName => $expectedDisplayName) {
                $signal = new RiskSignal(
                    name: $signalName,
                    score: new Score(0.5),
                    scope: SignalScope::DeviceNetwork,
                );

                expect($signal->getDisplayName())->toBe($expectedDisplayName);
            }
        });

        it('returns correct display names for document signals', function (): void {
            $documentSignals = [
                'id_age' => 'ID Age',
                'id_face_match_score' => 'ID Face Match Score',
                'id_barcode_status' => 'ID Barcode Status',
                'id_face_status' => 'ID Face Status',
                'id_text_status' => 'ID Text Status',
                'is_id_digital_spoof' => 'ID Digital Spoof',
                'is_full_id_captured' => 'Full ID Captured',
                'id_validity' => 'ID Validity',
            ];

            foreach ($documentSignals as $signalName => $expectedDisplayName) {
                $signal = new RiskSignal(
                    name: $signalName,
                    score: new Score(0.8),
                    scope: SignalScope::Document,
                );

                expect($signal->getDisplayName())->toBe($expectedDisplayName);
            }
        });

        it('returns correct display names for session signals', function (): void {
            $sessionSignals = [
                'impossible_travel' => 'Impossible Travel',
                'ip_mismatch' => 'IP Mismatch',
                'user_agent_mismatch' => 'User Agent Mismatch',
                'device_timezone_mismatch' => 'Device Timezone Mismatch',
                'ip_timezone_mismatch' => 'IP Timezone Mismatch',
            ];

            foreach ($sessionSignals as $signalName => $expectedDisplayName) {
                $signal = new RiskSignal(
                    name: $signalName,
                    score: new Score(0.3),
                    scope: SignalScope::ReferringSession,
                );

                expect($signal->getDisplayName())->toBe($expectedDisplayName);
            }
        });

        it('handles unknown signal names gracefully', function (): void {
            $unknownSignal = new RiskSignal(
                name: 'unknown_custom_signal',
                score: new Score(0.6),
                scope: SignalScope::DeviceNetwork,
            );

            expect($unknownSignal->getDisplayName())->toBe('Unknown Custom Signal');
        });

        it('handles signal names with numbers and special cases', function (): void {
            $specialSignals = [
                'signal_v2' => 'Signal V2',
                'test_signal_123' => 'Test Signal 123',
                'signal_with_many_underscores' => 'Signal With Many Underscores',
            ];

            foreach ($specialSignals as $signalName => $expectedDisplayName) {
                $signal = new RiskSignal(
                    name: $signalName,
                    score: new Score(0.7),
                    scope: SignalScope::DeviceNetwork,
                );

                expect($signal->getDisplayName())->toBe($expectedDisplayName);
            }
        });
    });

    describe('description functionality', function (): void {
        it('returns correct descriptions for device network signals', function (): void {
            $deviceSignals = [
                'device_risk' => 'Overall device risk assessment',
                'proxy' => 'Connection through proxy server detected',
                'vpn' => 'VPN usage detected',
                'datacenter' => 'Connection from datacenter IP address',
                'tor' => 'Connection through Tor network',
            ];

            foreach ($deviceSignals as $signalName => $expectedDescription) {
                $signal = new RiskSignal(
                    name: $signalName,
                    score: new Score(0.5),
                    scope: SignalScope::DeviceNetwork,
                );

                expect($signal->getDescription())->toBe($expectedDescription);
            }
        });

        it('returns correct descriptions for document signals', function (): void {
            $documentSignals = [
                'id_age' => 'Age of the identity document',
                'id_face_match_score' => 'Face match score between selfie and ID',
                'id_barcode_status' => 'Status of ID barcode verification',
                'id_validity' => 'Overall validity of the ID document',
            ];

            foreach ($documentSignals as $signalName => $expectedDescription) {
                $signal = new RiskSignal(
                    name: $signalName,
                    score: new Score(0.8),
                    scope: SignalScope::Document,
                );

                expect($signal->getDescription())->toBe($expectedDescription);
            }
        });

        it('returns correct descriptions for session signals', function (): void {
            $sessionSignals = [
                'impossible_travel' => 'Impossible travel pattern detected',
                'ip_mismatch' => 'IP address mismatch between sessions',
                'user_agent_mismatch' => 'User agent mismatch between sessions',
            ];

            foreach ($sessionSignals as $signalName => $expectedDescription) {
                $signal = new RiskSignal(
                    name: $signalName,
                    score: new Score(0.3),
                    scope: SignalScope::ReferringSession,
                );

                expect($signal->getDescription())->toBe($expectedDescription);
            }
        });

        it('handles unknown signal descriptions', function (): void {
            $unknownSignal = new RiskSignal(
                name: 'custom_unknown_signal',
                score: new Score(0.6),
                scope: SignalScope::DeviceNetwork,
            );

            expect($unknownSignal->getDescription())->toBe('Risk signal: custom_unknown_signal');
        });
    });

    describe('factory method fromScore', function (): void {
        it('can create RiskSignal from float score', function (): void {
            $signal = RiskSignal::fromScore('proxy', 0.85);

            expect($signal)->toBeInstanceOf(RiskSignal::class)
                ->and($signal->name)->toBe('proxy')
                ->and($signal->score->value)->toBe(0.85)
                ->and($signal->scope)->toBe(SignalScope::DeviceNetwork);
        });

        it('automatically determines correct scope for known signals', function (): void {
            $testCases = [
                ['device_risk', SignalScope::DeviceNetwork],
                ['id_validity', SignalScope::Document],
                ['impossible_travel', SignalScope::ReferringSession],
                ['account_score', SignalScope::Account],
                ['session_risk', SignalScope::Session],
            ];

            foreach ($testCases as [$signalName, $expectedScope]) {
                $signal = RiskSignal::fromScore($signalName, 0.5);

                expect($signal->name)->toBe($signalName)
                    ->and($signal->scope)->toBe($expectedScope);
            }
        });

        it('defaults to DeviceNetwork scope for unknown signals', function (): void {
            $unknownSignal = RiskSignal::fromScore('unknown_signal', 0.7);

            expect($unknownSignal->scope)->toBe(SignalScope::DeviceNetwork);
        });

        it('handles various score values', function (): void {
            $scoreValues = [0.0, 0.25, 0.5, 0.75, 1.0];

            foreach ($scoreValues as $scoreValue) {
                $signal = RiskSignal::fromScore('test_signal', $scoreValue);

                expect($signal->score->value)->toBe($scoreValue);
            }
        });
    });

    describe('serialization with GraniteDTO', function (): void {
        it('can be serialized to array', function (): void {
            $score = new Score(0.78);
            $riskSignal = new RiskSignal(
                name: 'vpn',
                score: $score,
                scope: SignalScope::DeviceNetwork,
            );

            $array = $riskSignal->array();

            expect($array)->toBeArray()
                ->and($array)->toHaveKeys(['name', 'score', 'scope', 'display_name', 'description'])
                ->and($array['name'])->toBe('vpn')
                ->and($array['score'])->toBe($score)
                ->and($array['scope'])->toBe('device_network')
                ->and($array['display_name'])->toBe('VPN')
                ->and($array['description'])->toBe('VPN usage detected');
        });

        it('can be created from array', function (): void {
            $data = [
                'name' => 'id_validity',
                'score' => ['value' => 0.92],
                'scope' => 'document',
            ];

            $riskSignal = RiskSignal::from($data);

            expect($riskSignal)->toBeInstanceOf(RiskSignal::class)
                ->and($riskSignal->name)->toBe('id_validity')
                ->and($riskSignal->score->value)->toBe(0.92)
                ->and($riskSignal->scope)->toBe(SignalScope::Document);
        });

        it('maintains consistency through serialization roundtrip', function (): void {
            $original = new RiskSignal(
                name: 'impossible_travel',
                score: new Score(0.88),
                scope: SignalScope::ReferringSession,
            );

            $array = $original->array();
            $restored = RiskSignal::from($array);

            expect($restored->name)->toBe($original->name)
                ->and($restored->score->value)->toBe($original->score->value)
                ->and($restored->scope)->toBe($original->scope);
        });

        it('can be created from JSON string', function (): void {
            $json = '{"name":"tor","score":{"value":0.95},"scope":"device_network"}';

            $riskSignal = RiskSignal::from($json);

            expect($riskSignal)->toBeInstanceOf(RiskSignal::class)
                ->and($riskSignal->name)->toBe('tor')
                ->and($riskSignal->score->value)->toBe(0.95)
                ->and($riskSignal->scope)->toBe(SignalScope::DeviceNetwork);
        });
    });

    describe('risk assessment scenarios', function (): void {
        it('handles high-risk signals', function (): void {
            $highRiskSignals = [
                'tor' => 0.95,
                'recent_fraud_ip' => 0.90,
                'is_id_digital_spoof' => 0.92,
                'impossible_travel' => 0.88,
            ];

            foreach ($highRiskSignals as $signalName => $scoreValue) {
                $signal = RiskSignal::fromScore($signalName, $scoreValue);

                expect($signal->score->value)->toBeGreaterThan(0.85)
                    ->and($signal->getDisplayName())->toBeString()
                    ->and($signal->getDescription())->toBeString();
            }
        });

        it('handles low-risk signals', function (): void {
            $lowRiskSignals = [
                'device_risk' => 0.05,
                'id_age' => 0.10,
                'ip_mismatch' => 0.15,
            ];

            foreach ($lowRiskSignals as $signalName => $scoreValue) {
                $signal = RiskSignal::fromScore($signalName, $scoreValue);

                expect($signal->score->value)->toBeLessThan(0.20)
                    ->and($signal->getDisplayName())->toBeString()
                    ->and($signal->getDescription())->toBeString();
            }
        });

        it('handles medium-risk signals', function (): void {
            $mediumRiskSignals = [
                'proxy' => 0.45,
                'vpn' => 0.55,
                'id_face_match_score' => 0.60,
            ];

            foreach ($mediumRiskSignals as $signalName => $scoreValue) {
                $signal = RiskSignal::fromScore($signalName, $scoreValue);

                expect($signal->score->value)->toBeGreaterThan(0.40)
                    ->and($signal->score->value)->toBeLessThan(0.70);
            }
        });
    });

    describe('scope categorization', function (): void {
        it('correctly categorizes all device network signals', function (): void {
            $deviceNetworkSignals = [
                'device_risk', 'proxy', 'vpn', 'datacenter', 'tor',
                'spoofed_ip', 'recent_fraud_ip', 'device_network_mismatch',
                'location_spoofing',
            ];

            foreach ($deviceNetworkSignals as $signalName) {
                $signal = RiskSignal::fromScore($signalName, 0.5);
                expect($signal->scope)->toBe(SignalScope::DeviceNetwork);
            }
        });

        it('correctly categorizes all document signals', function (): void {
            $documentSignals = [
                'id_age', 'id_face_match_score', 'id_barcode_status',
                'id_face_status', 'id_text_status', 'is_id_digital_spoof',
                'is_full_id_captured', 'id_validity',
            ];

            foreach ($documentSignals as $signalName) {
                $signal = RiskSignal::fromScore($signalName, 0.5);
                expect($signal->scope)->toBe(SignalScope::Document);
            }
        });

        it('correctly categorizes all session signals', function (): void {
            $sessionSignals = [
                'impossible_travel', 'ip_mismatch', 'user_agent_mismatch',
                'device_timezone_mismatch', 'ip_timezone_mismatch',
            ];

            foreach ($sessionSignals as $signalName) {
                $signal = RiskSignal::fromScore($signalName, 0.5);
                expect($signal->scope)->toBe(SignalScope::ReferringSession);
            }
        });
    });

    describe('real-world usage patterns', function (): void {
        it('supports fraud detection workflow', function (): void {
            $fraudSignals = [
                RiskSignal::fromScore('tor', 0.95),
                RiskSignal::fromScore('recent_fraud_ip', 0.88),
                RiskSignal::fromScore('is_id_digital_spoof', 0.92),
                RiskSignal::fromScore('impossible_travel', 0.85),
            ];

            foreach ($fraudSignals as $signal) {
                expect($signal->score->value)->toBeGreaterThan(0.8)
                    ->and($signal->getDisplayName())->toBeString()
                    ->and($signal->getDescription())->toBeString();
            }
        });

        it('supports risk scoring aggregation', function (): void {
            $signals = [
                RiskSignal::fromScore('device_risk', 0.3),
                RiskSignal::fromScore('proxy', 0.7),
                RiskSignal::fromScore('id_validity', 0.9),
            ];

            $totalRisk = 0;
            $count = 0;

            foreach ($signals as $signal) {
                $totalRisk += $signal->score->value;
                $count++;
            }

            $averageRisk = $totalRisk / $count;

            expect($averageRisk)->toBeFloat()
                ->and($averageRisk)->toBeGreaterThan(0.0)
                ->and($averageRisk)->toBeLessThan(1.0);
        });

        it('supports risk signal filtering by scope', function (): void {
            $allSignals = [
                RiskSignal::fromScore('device_risk', 0.3),
                RiskSignal::fromScore('id_validity', 0.8),
                RiskSignal::fromScore('impossible_travel', 0.6),
                RiskSignal::fromScore('proxy', 0.4),
            ];

            $deviceSignals = array_filter(
                $allSignals,
                fn($signal) => SignalScope::DeviceNetwork === $signal->scope,
            );

            $documentSignals = array_filter(
                $allSignals,
                fn($signal) => SignalScope::Document === $signal->scope,
            );

            expect($deviceSignals)->toHaveCount(2)
                ->and($documentSignals)->toHaveCount(1);
        });
    });
});

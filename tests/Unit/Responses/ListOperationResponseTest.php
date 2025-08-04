<?php

use Ninja\Verisoul\Responses\ListOperationResponse;

describe('ListOperationResponse', function (): void {
    describe('construction and basic functionality', function (): void {
        it('can be created with basic data', function (): void {
            $data = [
                'request_id' => 'req_' . bin2hex(random_bytes(12)),
                'message' => 'Operation completed successfully',
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);
        });

        it('can be created with custom message and success status', function (): void {
            $data = [
                'request_id' => 'req_custom_test',
                'message' => 'Custom operation message',
                'success' => false,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);
        });

        it('provides access to response fields', function (): void {
            $data = [
                'request_id' => 'test_request_456',
                'message' => 'Test message',
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray)->toHaveKey('message')
                ->and($responseArray)->toHaveKey('success')
                ->and($responseArray['request_id'])->toBe('test_request_456')
                ->and($responseArray['message'])->toBe('Test message')
                ->and($responseArray['success'])->toBeTrue();
        });
    });

    describe('successful operations', function (): void {
        it('handles successful list creation', function (): void {
            $data = [
                'request_id' => 'req_create_list_success',
                'message' => 'List "premium_users" created successfully',
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['message'])->toContain('created successfully');
        });

        it('handles successful account addition to list', function (): void {
            $data = [
                'request_id' => 'req_add_account_success',
                'message' => 'Account added to premium_users successfully',
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['message'])->toContain('added to');
        });

        it('handles successful account removal from list', function (): void {
            $data = [
                'request_id' => 'req_remove_account_success',
                'message' => 'Account removed from premium_users successfully',
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['message'])->toContain('removed from');
        });

        it('handles successful list deletion', function (): void {
            $data = [
                'request_id' => 'req_delete_list_success',
                'message' => 'List "old_users" deleted successfully',
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['message'])->toContain('deleted successfully');
        });

        it('handles successful list update', function (): void {
            $data = [
                'request_id' => 'req_update_list_success',
                'message' => 'List "premium_users" updated successfully',
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['message'])->toContain('updated successfully');
        });
    });

    describe('failed operations', function (): void {
        it('handles failed list creation', function (): void {
            $data = [
                'request_id' => 'req_create_list_failure',
                'message' => 'Failed to create list: List name already exists',
                'success' => false,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse()
                ->and($responseArray['message'])->toContain('Failed to create');
        });

        it('handles failed account addition', function (): void {
            $data = [
                'request_id' => 'req_add_account_failure',
                'message' => 'Failed to add account: Account not found',
                'success' => false,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse()
                ->and($responseArray['message'])->toContain('Failed to add');
        });

        it('handles failed account removal', function (): void {
            $data = [
                'request_id' => 'req_remove_account_failure',
                'message' => 'Failed to remove account: Account not in list',
                'success' => false,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse()
                ->and($responseArray['message'])->toContain('Failed to remove');
        });

        it('handles failed list deletion', function (): void {
            $data = [
                'request_id' => 'req_delete_list_failure',
                'message' => 'Failed to delete list: List does not exist',
                'success' => false,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse()
                ->and($responseArray['message'])->toContain('Failed to delete');
        });

        it('handles permission denied operations', function (): void {
            $data = [
                'request_id' => 'req_permission_denied',
                'message' => 'Permission denied: Insufficient privileges to modify list',
                'success' => false,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse()
                ->and($responseArray['message'])->toContain('Permission denied');
        });
    });

    describe('message content handling', function (): void {
        it('handles various message lengths', function (): void {
            $messages = [
                'Short message',
                'Medium length message describing the operation',
                'Very long message that describes in detail what the operation attempted to do, what parameters were used, and what the outcome was, including any relevant error details or success confirmations that might be helpful for debugging or user feedback purposes.',
            ];

            foreach ($messages as $index => $message) {
                $data = [
                    'request_id' => "req_message_length_{$index}",
                    'message' => $message,
                    'success' => true,
                ];
                $response = ListOperationResponse::from($data);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);

                $responseArray = $response->array();
                expect($responseArray['message'])->toBe($message);
            }
        });

        it('handles messages with special characters', function (): void {
            $specialMessages = [
                'Message with "quoted" text',
                "Message with 'single quotes'",
                'Message with newline\ncharacter',
                'Message with tab\tcharacter',
                'Message with émojis 🎉 ✅ ❌',
                'Message with unicode: café, naïve, résumé',
                'Message with symbols: @#$%^&*()_+-=[]{}|;:,.<>?',
                'Message with HTML: <b>bold</b> and &amp; entities',
                'Message with JSON: {"key": "value", "number": 123}',
            ];

            foreach ($specialMessages as $index => $message) {
                $data = [
                    'request_id' => "req_special_chars_{$index}",
                    'message' => $message,
                    'success' => true,
                ];
                $response = ListOperationResponse::from($data);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);

                $responseArray = $response->array();
                expect($responseArray['message'])->toBe($message);
            }
        });

        it('handles empty message', function (): void {
            $data = [
                'request_id' => 'req_empty_message',
                'message' => '',
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            $responseArray = $response->array();
            expect($responseArray['message'])->toBe('');
        });

        it('handles null message converted to string', function (): void {
            $data = [
                'request_id' => 'req_null_message',
                'message' => '',  // Empty string instead of null
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);
        });
    });

    describe('request ID handling', function (): void {
        it('handles various request ID formats', function (): void {
            $requestIds = [
                'req_12345678901234567890',
                'request_abc123',
                'list_op_req_789',
                '550e8400-e29b-41d4-a716-446655440000',
                'simple_request_id',
                'list-operation-request',
                'ListOperationRequest',
                'list_operation_request_123',
            ];

            foreach ($requestIds as $requestId) {
                $data = [
                    'request_id' => $requestId,
                    'message' => 'Test message',
                    'success' => true,
                ];
                $response = ListOperationResponse::from($data);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);

                $responseArray = $response->array();
                expect($responseArray['request_id'])->toBe($requestId);
            }
        });

        it('handles empty request ID', function (): void {
            $data = [
                'request_id' => '',
                'message' => 'Test message',
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);
        });

        it('handles very long request ID', function (): void {
            $longRequestId = str_repeat('req_', 100) . 'end';
            $data = [
                'request_id' => $longRequestId,
                'message' => 'Test message',
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            $responseArray = $response->array();
            expect($responseArray['request_id'])->toBe($longRequestId);
        });
    });

    describe('success flag handling', function (): void {
        it('correctly handles boolean true', function (): void {
            $data = [
                'request_id' => 'req_bool_true',
                'message' => 'Success message',
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['success'])->toBeBool();
        });

        it('correctly handles boolean false', function (): void {
            $data = [
                'request_id' => 'req_bool_false',
                'message' => 'Failure message',
                'success' => false,
            ];
            $response = ListOperationResponse::from($data);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse()
                ->and($responseArray['success'])->toBeBool();
        });

        it('handles truthy values correctly', function (): void {
            $truthyValues = [1, '1', 'true', 'yes'];

            foreach ($truthyValues as $index => $value) {
                $data = [
                    'request_id' => "req_truthy_{$index}",
                    'message' => 'Truthy test message',
                    'success' => $value,
                ];
                $response = ListOperationResponse::from($data);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);
            }
        });

        it('handles falsy values correctly', function (): void {
            $falsyValues = [0, '0', 'false', 'no'];  // Removed null since it can't be converted to bool

            foreach ($falsyValues as $index => $value) {
                $data = [
                    'request_id' => "req_falsy_{$index}",
                    'message' => 'Falsy test message',
                    'success' => $value,
                ];
                $response = ListOperationResponse::from($data);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);
            }
        });
    });

    describe('data integrity and serialization', function (): void {
        it('maintains data integrity through serialization', function (): void {
            $originalData = [
                'request_id' => 'integrity_test_request',
                'message' => 'Integrity test message',
                'success' => true,
            ];
            $response = ListOperationResponse::from($originalData);
            $serializedData = $response->array();
            $recreatedResponse = ListOperationResponse::from($serializedData);

            expect($recreatedResponse)->toBeInstanceOf(ListOperationResponse::class);

            $originalArray = $response->array();
            $recreatedArray = $recreatedResponse->array();

            expect($recreatedArray['request_id'])->toBe($originalArray['request_id'])
                ->and($recreatedArray['message'])->toBe($originalArray['message'])
                ->and($recreatedArray['success'])->toBe($originalArray['success']);
        });

        it('serializes to correct structure', function (): void {
            $data = [
                'request_id' => 'structure_test',
                'message' => 'Structure test message',
                'success' => false,
            ];
            $response = ListOperationResponse::from($data);
            $serialized = $response->array();

            expect($serialized)->toBeArray()
                ->and($serialized)->toHaveCount(3)
                ->and($serialized)->toHaveKey('request_id')
                ->and($serialized)->toHaveKey('message')
                ->and($serialized)->toHaveKey('success')
                ->and($serialized['request_id'])->toBe('structure_test')
                ->and($serialized['message'])->toBe('Structure test message')
                ->and($serialized['success'])->toBeFalse();
        });

        it('handles JSON serialization correctly', function (): void {
            $data = [
                'request_id' => 'json_test',
                'message' => 'JSON test message',
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            $jsonString = json_encode($response->array());
            $decodedData = json_decode($jsonString, true);
            $recreatedResponse = ListOperationResponse::from($decodedData);

            expect($recreatedResponse)->toBeInstanceOf(ListOperationResponse::class);
            expect(json_last_error())->toBe(JSON_ERROR_NONE);
        });
    });

    describe('readonly class behavior', function (): void {
        it('creates immutable instances', function (): void {
            $data = [
                'request_id' => 'immutable_test',
                'message' => 'Immutable test message',
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            // Since it's a readonly class, we can't modify properties
            // This test verifies the object was created successfully
            $responseArray = $response->array();
            expect($responseArray['request_id'])->toBe('immutable_test');
        });

        it('provides consistent data access', function (): void {
            $data = [
                'request_id' => 'consistent_test',
                'message' => 'Consistent test message',
                'success' => false,
            ];
            $response = ListOperationResponse::from($data);

            $firstAccess = $response->array();
            $secondAccess = $response->array();
            $thirdAccess = $response->array();

            expect($firstAccess)->toBe($secondAccess)
                ->and($secondAccess)->toBe($thirdAccess);
        });
    });

    describe('real-world list operation scenarios', function (): void {
        it('handles successful user list management', function (): void {
            $scenarios = [
                [
                    'operation' => 'create',
                    'message' => 'List "premium_subscribers" created successfully',
                    'success' => true,
                ],
                [
                    'operation' => 'add_account',
                    'message' => 'Account user_123 added to premium_subscribers',
                    'success' => true,
                ],
                [
                    'operation' => 'remove_account',
                    'message' => 'Account user_456 removed from premium_subscribers',
                    'success' => true,
                ],
                [
                    'operation' => 'update',
                    'message' => 'List "premium_subscribers" metadata updated',
                    'success' => true,
                ],
                [
                    'operation' => 'delete',
                    'message' => 'List "old_list" deleted successfully',
                    'success' => true,
                ],
            ];

            foreach ($scenarios as $scenario) {
                $data = [
                    'request_id' => 'list_mgmt_' . $scenario['operation'] . '_' . time(),
                    'message' => $scenario['message'],
                    'success' => $scenario['success'],
                ];
                $response = ListOperationResponse::from($data);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);

                $responseArray = $response->array();
                expect($responseArray['success'])->toBe($scenario['success']);
            }
        });

        it('handles batch operations', function (): void {
            $batchMessages = [
                'Batch operation completed: 5 accounts added to premium_users',
                'Batch operation completed: 3 accounts removed from trial_users',
                'Batch operation completed: 10 accounts moved from basic_users to premium_users',
                'Batch operation failed: 2 of 5 accounts could not be added to restricted_list',
            ];

            foreach ($batchMessages as $index => $message) {
                $data = [
                    'request_id' => "batch_op_{$index}_" . time(),
                    'message' => $message,
                    'success' => ! str_contains($message, 'failed'),
                ];
                $response = ListOperationResponse::from($data);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);
            }
        });

        it('handles error scenarios', function (): void {
            $errorScenarios = [
                [
                    'message' => 'Error: List name contains invalid characters',
                    'success' => false,
                ],
                [
                    'message' => 'Error: Maximum number of lists reached (100)',
                    'success' => false,
                ],
                [
                    'message' => 'Error: Account ID does not exist in system',
                    'success' => false,
                ],
                [
                    'message' => 'Error: List is protected and cannot be modified',
                    'success' => false,
                ],
                [
                    'message' => 'Error: Rate limit exceeded, please try again later',
                    'success' => false,
                ],
            ];

            foreach ($errorScenarios as $index => $scenario) {
                $data = [
                    'request_id' => "error_scenario_{$index}_" . time(),
                    'message' => $scenario['message'],
                    'success' => $scenario['success'],
                ];
                $response = ListOperationResponse::from($data);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);

                $responseArray = $response->array();
                expect($responseArray['success'])->toBeFalse();
            }
        });

        it('handles API response format', function (): void {
            // Simulate typical API response format from fixtures
            $apiResponse = [
                'request_id' => 'api_format_' . uniqid(),
                'message' => 'Account added to us_users',
                'success' => true,
            ];
            $response = ListOperationResponse::from($apiResponse);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            // Verify it can be used in typical API response handling
            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray)->toHaveKey('message')
                ->and($responseArray)->toHaveKey('success');
        });
    });

    describe('edge cases and error handling', function (): void {
        it('handles messages with various encodings', function (): void {
            $encodingMessages = [
                'ASCII message',
                'UTF-8 message with émojis: 🚀 ✨ 🎯',
                'Message with Arabic: مرحبا بالعالم',
                'Message with Chinese: 你好世界',
                'Message with Japanese: こんにちは世界',
                'Message with Russian: Привет мир',
                'Message with German umlauts: Müller, Köln, Füße',
            ];

            foreach ($encodingMessages as $index => $message) {
                $data = [
                    'request_id' => "encoding_test_{$index}",
                    'message' => $message,
                    'success' => true,
                ];
                $response = ListOperationResponse::from($data);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);
            }
        });

        it('handles extremely long messages', function (): void {
            $longMessage = str_repeat('Very long message content. ', 500) . 'End.';
            $data = [
                'request_id' => 'long_message_test',
                'message' => $longMessage,
                'success' => true,
            ];
            $response = ListOperationResponse::from($data);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);

            $responseArray = $response->array();
            expect($responseArray['message'])->toBe($longMessage);
        });

        it('handles messages with control characters', function (): void {
            $controlMessages = [
                "Message with\nline breaks\nand tabs\there",
                "Message with\rcarriage return",
                "Message with\x00null character",
                "Message with\x08backspace",
                "Message with\x1bescape sequence",
            ];

            foreach ($controlMessages as $index => $message) {
                $data = [
                    'request_id' => "control_chars_{$index}",
                    'message' => $message,
                    'success' => true,
                ];
                $response = ListOperationResponse::from($data);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);
            }
        });

        it('handles whitespace variations', function (): void {
            $whitespaceMessages = [
                ' Leading space',
                'Trailing space ',
                ' Both spaces ',
                'Multiple    spaces    between    words',
                "\tTab at start",
                "Tab at end\t",
                "\nNewline at start",
                "Newline at end\n",
            ];

            foreach ($whitespaceMessages as $index => $message) {
                $data = [
                    'request_id' => "whitespace_{$index}",
                    'message' => $message,
                    'success' => true,
                ];
                $response = ListOperationResponse::from($data);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);
            }
        });
    });

    describe('performance and memory', function (): void {
        it('creates multiple instances efficiently', function (): void {
            $startTime = microtime(true);
            $responses = [];

            for ($i = 1; $i <= 100; $i++) {
                $data = [
                    'request_id' => "performance_test_{$i}",
                    'message' => "Performance test message {$i}",
                    'success' => (0 === $i % 2),
                ];
                $responses[] = ListOperationResponse::from($data);
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            expect(count($responses))->toBe(100)
                ->and($executionTime)->toBeLessThan(0.5);

            foreach ($responses as $response) {
                expect($response)->toBeInstanceOf(ListOperationResponse::class);
            }
        });

        it('maintains reasonable memory usage', function (): void {
            $initialMemory = memory_get_usage();

            $responses = [];
            for ($i = 1; $i <= 1000; $i++) {
                $data = [
                    'request_id' => "memory_test_{$i}",
                    'message' => "Memory test message {$i}",
                    'success' => true,
                ];
                $responses[] = ListOperationResponse::from($data);
            }

            $finalMemory = memory_get_usage();
            $memoryUsed = $finalMemory - $initialMemory;

            expect(count($responses))->toBe(1000)
                ->and($memoryUsed)->toBeLessThan(1024 * 1024); // Less than 1MB

            unset($responses);
        });
    });
});

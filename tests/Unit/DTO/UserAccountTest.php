<?php

use Ninja\Verisoul\DTO\UserAccount;

describe('UserAccount DTO', function () {
    describe('construction', function () {
        it('can be created with all properties', function () {
            $userAccount = new UserAccount(
                id: 'user_123',
                email: 'test@example.com',
                metadata: ['role' => 'admin', 'department' => 'engineering'],
                group: 'premium'
            );

            expect($userAccount->id)->toBe('user_123')
                ->and($userAccount->email)->toBe('test@example.com')
                ->and($userAccount->metadata)->toBe(['role' => 'admin', 'department' => 'engineering'])
                ->and($userAccount->group)->toBe('premium');
        });

        it('can be created with minimal properties', function () {
            $userAccount = new UserAccount(id: 'user_456');

            expect($userAccount->id)->toBe('user_456')
                ->and($userAccount->email)->toBeNull()
                ->and($userAccount->metadata)->toBe([])
                ->and($userAccount->group)->toBeNull();
        });

        it('can be created with partial data', function () {
            $userAccount = new UserAccount(
                id: 'user_789',
                email: 'partial@example.com'
            );

            expect($userAccount->id)->toBe('user_789')
                ->and($userAccount->email)->toBe('partial@example.com')
                ->and($userAccount->metadata)->toBe([])
                ->and($userAccount->group)->toBeNull();
        });

        it('handles empty metadata array', function () {
            $userAccount = new UserAccount(
                id: 'user_empty',
                metadata: []
            );

            expect($userAccount->metadata)->toBe([])
                ->and($userAccount->metadata)->toBeArray()
                ->and($userAccount->metadata)->toHaveCount(0);
        });
    });

    describe('immutability', function () {
        it('is readonly and immutable', function () {
            $userAccount = new UserAccount(
                id: 'user_test',
                email: 'test@example.com',
                metadata: ['key' => 'value'],
                group: 'test'
            );

            $reflection = new ReflectionClass($userAccount);
            $properties = $reflection->getProperties();

            foreach ($properties as $property) {
                expect($property->isReadOnly())->toBeTrue(
                    "Property {$property->getName()} should be readonly"
                );
            }
        });

        it('maintains data integrity', function () {
            $userAccount = new UserAccount(
                id: 'user_integrity',
                email: 'integrity@example.com'
            );

            expect($userAccount->id)->toBe('user_integrity');
            expect($userAccount->id)->toBe('user_integrity'); // Second call
            expect($userAccount->email)->toBe('integrity@example.com');
            expect($userAccount->email)->toBe('integrity@example.com'); // Second call
        });
    });

    describe('serialization with GraniteDTO', function () {
        it('can be serialized to array', function () {
            $userAccount = new UserAccount(
                id: 'user_serial',
                email: 'serial@example.com',
                metadata: ['type' => 'test', 'priority' => 'high'],
                group: 'serialization'
            );

            $array = $userAccount->array();

            expect($array)->toBeArray()
                ->and($array)->toHaveKeys(['id', 'email', 'metadata', 'group'])
                ->and($array['id'])->toBe('user_serial')
                ->and($array['email'])->toBe('serial@example.com')
                ->and($array['metadata'])->toBe(['type' => 'test', 'priority' => 'high'])
                ->and($array['group'])->toBe('serialization');
        });

        it('can be created from array', function () {
            $data = [
                'id' => 'user_from_array',
                'email' => 'fromarray@example.com',
                'metadata' => ['source' => 'array'],
                'group' => 'array_test',
            ];

            $userAccount = UserAccount::from($data);

            expect($userAccount)->toBeInstanceOf(UserAccount::class)
                ->and($userAccount->id)->toBe('user_from_array')
                ->and($userAccount->email)->toBe('fromarray@example.com')
                ->and($userAccount->metadata)->toBe(['source' => 'array'])
                ->and($userAccount->group)->toBe('array_test');
        });

        it('maintains consistency through serialization roundtrip', function () {
            $original = new UserAccount(
                id: 'user_roundtrip',
                email: null,
                metadata: ['test' => true, 'number' => 42],
                group: 'roundtrip'
            );

            $array = $original->array();
            $restored = UserAccount::from($array);

            expect($restored->id)->toBe($original->id)
                ->and($restored->email)->toBe($original->email)
                ->and($restored->metadata)->toBe($original->metadata)
                ->and($restored->group)->toBe($original->group);
        });

        it('can be created from JSON string', function () {
            $json = '{"id":"user_json","email":"json@example.com","metadata":{"format":"json"},"group":"json_test"}';
            
            $userAccount = UserAccount::from($json);

            expect($userAccount)->toBeInstanceOf(UserAccount::class)
                ->and($userAccount->id)->toBe('user_json')
                ->and($userAccount->email)->toBe('json@example.com')
                ->and($userAccount->metadata)->toBe(['format' => 'json'])
                ->and($userAccount->group)->toBe('json_test');
        });

        it('handles null values in serialization', function () {
            $userAccount = new UserAccount(
                id: 'user_nulls',
                email: null,
                metadata: [],
                group: null
            );

            $array = $userAccount->array();

            expect($array['id'])->toBe('user_nulls')
                ->and($array['email'])->toBeNull()
                ->and($array['metadata'])->toBe([])
                ->and($array['group'])->toBeNull();
        });
    });

    describe('metadata handling', function () {
        it('handles complex metadata structures', function () {
            $complexMetadata = [
                'user_preferences' => [
                    'theme' => 'dark',
                    'language' => 'en',
                    'notifications' => ['email', 'sms']
                ],
                'account_info' => [
                    'created_at' => '2023-01-01',
                    'last_login' => '2023-12-01',
                    'login_count' => 150
                ],
                'flags' => ['verified', 'premium', 'beta_tester']
            ];

            $userAccount = new UserAccount(
                id: 'user_complex',
                metadata: $complexMetadata
            );

            expect($userAccount->metadata)->toBe($complexMetadata)
                ->and($userAccount->metadata['user_preferences']['theme'])->toBe('dark')
                ->and($userAccount->metadata['account_info']['login_count'])->toBe(150)
                ->and($userAccount->metadata['flags'])->toContain('premium');
        });

        it('handles metadata with various data types', function () {
            $mixedMetadata = [
                'string_value' => 'text',
                'integer_value' => 42,
                'float_value' => 3.14,
                'boolean_value' => true,
                'null_value' => null,
                'array_value' => [1, 2, 3],
                'nested_object' => ['key' => 'value']
            ];

            $userAccount = new UserAccount(
                id: 'user_mixed',
                metadata: $mixedMetadata
            );

            expect($userAccount->metadata['string_value'])->toBe('text')
                ->and($userAccount->metadata['integer_value'])->toBe(42)
                ->and($userAccount->metadata['float_value'])->toBe(3.14)
                ->and($userAccount->metadata['boolean_value'])->toBeTrue()
                ->and($userAccount->metadata['null_value'])->toBeNull()
                ->and($userAccount->metadata['array_value'])->toBe([1, 2, 3])
                ->and($userAccount->metadata['nested_object'])->toBe(['key' => 'value']);
        });
    });

    describe('validation scenarios', function () {
        it('handles various ID formats', function () {
            $idFormats = [
                'user_123',
                'usr-456-789',
                'u_789_abc_def',
                '12345',
                'USER_CAPS',
                'user.with.dots',
                'user@domain.com',
                'urn:user:12345'
            ];

            foreach ($idFormats as $idFormat) {
                $userAccount = new UserAccount(id: $idFormat);
                expect($userAccount->id)->toBe($idFormat);
            }
        });

        it('handles various email formats', function () {
            $emailFormats = [
                'simple@example.com',
                'user+tag@domain.co.uk',
                'firstname.lastname@company.com',
                'user123@test-domain.net',
                'unusual@sub.domain.example.org'
            ];

            foreach ($emailFormats as $email) {
                $userAccount = new UserAccount(
                    id: 'user_email_test',
                    email: $email
                );
                expect($userAccount->email)->toBe($email);
            }
        });

        it('handles various group names', function () {
            $groupNames = [
                'free',
                'premium',
                'enterprise',
                'admin',
                'beta-testers',
                'group_with_underscores',
                'Group With Spaces',
                'group123',
                'dev-team-alpha'
            ];

            foreach ($groupNames as $group) {
                $userAccount = new UserAccount(
                    id: 'user_group_test',
                    group: $group
                );
                expect($userAccount->group)->toBe($group);
            }
        });
    });

    describe('edge cases and special scenarios', function () {
        it('handles very long IDs', function () {
            $longId = str_repeat('a', 255);
            $userAccount = new UserAccount(id: $longId);

            expect($userAccount->id)->toBe($longId)
                ->and(strlen($userAccount->id))->toBe(255);
        });

        it('handles very long emails', function () {
            $longLocalPart = str_repeat('a', 50);
            $longEmail = $longLocalPart . '@example.com';
            
            $userAccount = new UserAccount(
                id: 'user_long_email',
                email: $longEmail
            );

            expect($userAccount->email)->toBe($longEmail)
                ->and(strlen($userAccount->email))->toBeGreaterThan(50);
        });

        it('handles large metadata objects', function () {
            $largeMetadata = [];
            for ($i = 0; $i < 100; $i++) {
                $largeMetadata["key_$i"] = "value_$i";
            }

            $userAccount = new UserAccount(
                id: 'user_large_meta',
                metadata: $largeMetadata
            );

            expect($userAccount->metadata)->toHaveCount(100)
                ->and($userAccount->metadata['key_0'])->toBe('value_0')
                ->and($userAccount->metadata['key_99'])->toBe('value_99');
        });

        it('handles Unicode characters in all fields', function () {
            $userAccount = new UserAccount(
                id: 'user_测试_🚀',
                email: 'tëst@exãmple.cøm',
                metadata: ['名前' => 'テスト', '🎯' => '目标'],
                group: 'grüppe_α'
            );

            expect($userAccount->id)->toBe('user_测试_🚀')
                ->and($userAccount->email)->toBe('tëst@exãmple.cøm')
                ->and($userAccount->metadata['名前'])->toBe('テスト')
                ->and($userAccount->metadata['🎯'])->toBe('目标')
                ->and($userAccount->group)->toBe('grüppe_α');
        });
    });

    describe('real-world usage patterns', function () {
        it('supports typical user account data', function () {
            $userAccount = new UserAccount(
                id: 'auth0|user_12345',
                email: 'john.doe@company.com',
                metadata: [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'role' => 'software_engineer',
                    'department' => 'engineering',
                    'hire_date' => '2023-01-15',
                    'permissions' => ['read', 'write', 'admin']
                ],
                group: 'employees'
            );

            expect($userAccount->id)->toBe('auth0|user_12345')
                ->and($userAccount->email)->toBe('john.doe@company.com')
                ->and($userAccount->metadata['first_name'])->toBe('John')
                ->and($userAccount->metadata['permissions'])->toContain('admin')
                ->and($userAccount->group)->toBe('employees');
        });

        it('supports minimal anonymous user', function () {
            $anonymousUser = new UserAccount(id: 'anon_session_abc123');

            expect($anonymousUser->id)->toBe('anon_session_abc123')
                ->and($anonymousUser->email)->toBeNull()
                ->and($anonymousUser->metadata)->toBe([])
                ->and($anonymousUser->group)->toBeNull();
        });

        it('supports API integration patterns', function () {
            $apiUserAccount = new UserAccount(
                id: 'api_client_xyz789',
                email: 'api@service.com',
                metadata: [
                    'api_version' => 'v2',
                    'rate_limit' => 1000,
                    'scopes' => ['read:users', 'write:data'],
                    'created_by' => 'admin_user_123'
                ],
                group: 'api_clients'
            );

            expect($apiUserAccount->group)->toBe('api_clients')
                ->and($apiUserAccount->metadata['api_version'])->toBe('v2')
                ->and($apiUserAccount->metadata['rate_limit'])->toBe(1000)
                ->and($apiUserAccount->metadata['scopes'])->toContain('read:users');
        });
    });
});
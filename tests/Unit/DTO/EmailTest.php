<?php

use Ninja\Verisoul\DTO\Email;

describe('Email DTO', function (): void {
    describe('construction', function (): void {
        it('can be created with all required properties', function (): void {
            $email = new Email(
                email: 'test@example.com',
                personal: true,
                disposable: false,
                valid: true,
            );

            expect($email->email)->toBe('test@example.com')
                ->and($email->personal)->toBeTrue()
                ->and($email->disposable)->toBeFalse()
                ->and($email->valid)->toBeTrue();
        });

        it('can be created with business email', function (): void {
            $email = new Email(
                email: 'contact@company.com',
                personal: false,
                disposable: false,
                valid: true,
            );

            expect($email->email)->toBe('contact@company.com')
                ->and($email->personal)->toBeFalse()
                ->and($email->disposable)->toBeFalse()
                ->and($email->valid)->toBeTrue();
        });

        it('can be created with disposable email', function (): void {
            $email = new Email(
                email: 'temp@10minutemail.com',
                personal: true,
                disposable: true,
                valid: true,
            );

            expect($email->email)->toBe('temp@10minutemail.com')
                ->and($email->personal)->toBeTrue()
                ->and($email->disposable)->toBeTrue()
                ->and($email->valid)->toBeTrue();
        });

        it('can be created with invalid email', function (): void {
            $email = new Email(
                email: 'invalid-email',
                personal: false,
                disposable: false,
                valid: false,
            );

            expect($email->email)->toBe('invalid-email')
                ->and($email->personal)->toBeFalse()
                ->and($email->disposable)->toBeFalse()
                ->and($email->valid)->toBeFalse();
        });
    });

    describe('immutability', function (): void {
        it('is readonly and immutable', function (): void {
            $email = new Email(
                email: 'test@example.com',
                personal: true,
                disposable: false,
                valid: true,
            );

            $reflection = new ReflectionClass($email);
            $properties = $reflection->getProperties();

            foreach ($properties as $property) {
                expect($property->isReadOnly())->toBeTrue(
                    "Property {$property->getName()} should be readonly",
                );
            }
        });

        it('maintains data integrity', function (): void {
            $email = new Email(
                email: 'test@example.com',
                personal: true,
                disposable: false,
                valid: true,
            );

            // Verify data doesn't change
            expect($email->email)->toBe('test@example.com');
            expect($email->email)->toBe('test@example.com'); // Second call
            expect($email->personal)->toBeTrue();
            expect($email->personal)->toBeTrue(); // Second call
        });
    });

    describe('serialization with GraniteDTO', function (): void {
        it('can be serialized to array', function (): void {
            $email = new Email(
                email: 'user@domain.com',
                personal: false,
                disposable: true,
                valid: true,
            );

            $array = $email->array();

            expect($array)->toBeArray()
                ->and($array)->toHaveKeys(['email', 'personal', 'disposable', 'valid'])
                ->and($array['email'])->toBe('user@domain.com')
                ->and($array['personal'])->toBeFalse()
                ->and($array['disposable'])->toBeTrue()
                ->and($array['valid'])->toBeTrue();
        });

        it('can be created from array', function (): void {
            $data = [
                'email' => 'contact@business.org',
                'personal' => false,
                'disposable' => false,
                'valid' => true,
            ];

            $email = Email::from($data);

            expect($email)->toBeInstanceOf(Email::class)
                ->and($email->email)->toBe('contact@business.org')
                ->and($email->personal)->toBeFalse()
                ->and($email->disposable)->toBeFalse()
                ->and($email->valid)->toBeTrue();
        });

        it('maintains consistency through serialization roundtrip', function (): void {
            $original = new Email(
                email: 'temp@disposable.net',
                personal: true,
                disposable: true,
                valid: false,
            );

            $array = $original->array();
            $restored = Email::from($array);

            expect($restored->email)->toBe($original->email)
                ->and($restored->personal)->toBe($original->personal)
                ->and($restored->disposable)->toBe($original->disposable)
                ->and($restored->valid)->toBe($original->valid);
        });

        it('can be created from JSON string', function (): void {
            $json = '{"email":"test@example.com","personal":true,"disposable":false,"valid":true}';

            $email = Email::from($json);

            expect($email)->toBeInstanceOf(Email::class)
                ->and($email->email)->toBe('test@example.com')
                ->and($email->personal)->toBeTrue()
                ->and($email->disposable)->toBeFalse()
                ->and($email->valid)->toBeTrue();
        });
    });

    describe('email validation scenarios', function (): void {
        it('handles various valid email formats', function (): void {
            $validEmails = [
                'simple@example.com',
                'user.name@domain.co.uk',
                'user+tag@example.org',
                'firstname.lastname@company.com',
                'email@subdomain.example.com',
                'user123@test-domain.net',
                'a@b.co',
            ];

            foreach ($validEmails as $emailAddress) {
                $email = new Email(
                    email: $emailAddress,
                    personal: true,
                    disposable: false,
                    valid: true,
                );

                expect($email->email)->toBe($emailAddress)
                    ->and($email->valid)->toBeTrue();
            }
        });

        it('handles common personal email providers', function (): void {
            $personalEmails = [
                'user@gmail.com',
                'person@yahoo.com',
                'someone@hotmail.com',
                'individual@outlook.com',
                'me@icloud.com',
            ];

            foreach ($personalEmails as $emailAddress) {
                $email = new Email(
                    email: $emailAddress,
                    personal: true,
                    disposable: false,
                    valid: true,
                );

                expect($email->email)->toBe($emailAddress)
                    ->and($email->personal)->toBeTrue()
                    ->and($email->disposable)->toBeFalse();
            }
        });

        it('handles business email patterns', function (): void {
            $businessEmails = [
                'contact@company.com',
                'support@business.org',
                'info@startup.io',
                'sales@enterprise.net',
                'admin@organization.gov',
            ];

            foreach ($businessEmails as $emailAddress) {
                $email = new Email(
                    email: $emailAddress,
                    personal: false,
                    disposable: false,
                    valid: true,
                );

                expect($email->email)->toBe($emailAddress)
                    ->and($email->personal)->toBeFalse()
                    ->and($email->disposable)->toBeFalse();
            }
        });

        it('handles disposable email providers', function (): void {
            $disposableEmails = [
                'temp@10minutemail.com',
                'test@mailinator.com',
                'throwaway@guerrillamail.com',
                'temp@tempmail.org',
                'disposable@yopmail.com',
            ];

            foreach ($disposableEmails as $emailAddress) {
                $email = new Email(
                    email: $emailAddress,
                    personal: true,
                    disposable: true,
                    valid: true,
                );

                expect($email->email)->toBe($emailAddress)
                    ->and($email->disposable)->toBeTrue()
                    ->and($email->valid)->toBeTrue();
            }
        });

        it('handles invalid email formats', function (): void {
            $invalidEmails = [
                'invalid',
                '@domain.com',
                'user@',
                'user@.com',
                'user..double.dot@example.com',
                'user@domain',
                '',
            ];

            foreach ($invalidEmails as $emailAddress) {
                $email = new Email(
                    email: $emailAddress,
                    personal: false,
                    disposable: false,
                    valid: false,
                );

                expect($email->email)->toBe($emailAddress)
                    ->and($email->valid)->toBeFalse();
            }
        });
    });

    describe('boolean combinations', function (): void {
        it('handles all valid boolean combinations', function (): void {
            $combinations = [
                [true, true, true],    // personal, disposable, valid
                [true, true, false],   // personal, disposable, invalid
                [true, false, true],   // personal, not disposable, valid
                [true, false, false],  // personal, not disposable, invalid
                [false, true, true],   // not personal, disposable, valid (rare but possible)
                [false, true, false],  // not personal, disposable, invalid
                [false, false, true],  // not personal, not disposable, valid
                [false, false, false], // not personal, not disposable, invalid
            ];

            foreach ($combinations as [$personal, $disposable, $valid]) {
                $email = new Email(
                    email: 'test@example.com',
                    personal: $personal,
                    disposable: $disposable,
                    valid: $valid,
                );

                expect($email->personal)->toBe($personal)
                    ->and($email->disposable)->toBe($disposable)
                    ->and($email->valid)->toBe($valid);
            }
        });
    });

    describe('international and edge cases', function (): void {
        it('handles international domain names', function (): void {
            $internationalEmails = [
                'user@café.com',
                'test@münchen.de',
                'contact@тест.рф',
                'info@例え.テスト',
            ];

            foreach ($internationalEmails as $emailAddress) {
                $email = new Email(
                    email: $emailAddress,
                    personal: true,
                    disposable: false,
                    valid: true,
                );

                expect($email->email)->toBe($emailAddress);
            }
        });

        it('handles email addresses with special characters', function (): void {
            $specialEmails = [
                'user+tag@example.com',
                'user.name@example.com',
                'user_name@example.com',
                'user-name@example.com',
                'user123@example.com',
            ];

            foreach ($specialEmails as $emailAddress) {
                $email = new Email(
                    email: $emailAddress,
                    personal: true,
                    disposable: false,
                    valid: true,
                );

                expect($email->email)->toBe($emailAddress)
                    ->and($email->valid)->toBeTrue();
            }
        });

        it('handles very long email addresses', function (): void {
            $longLocalPart = str_repeat('a', 60);
            $longEmail = $longLocalPart . '@example.com';

            $email = new Email(
                email: $longEmail,
                personal: true,
                disposable: false,
                valid: true,
            );

            expect($email->email)->toBe($longEmail)
                ->and(strlen($email->email))->toBeGreaterThan(60);
        });

        it('handles edge case with empty email', function (): void {
            $email = new Email(
                email: '',
                personal: false,
                disposable: false,
                valid: false,
            );

            expect($email->email)->toBe('')
                ->and($email->valid)->toBeFalse();
        });
    });

    describe('real-world classification scenarios', function (): void {
        it('correctly identifies personal Gmail account', function (): void {
            $email = new Email(
                email: 'johndoe123@gmail.com',
                personal: true,
                disposable: false,
                valid: true,
            );

            expect($email->personal)->toBeTrue()
                ->and($email->disposable)->toBeFalse()
                ->and($email->valid)->toBeTrue();
        });

        it('correctly identifies business domain', function (): void {
            $email = new Email(
                email: 'employee@techcompany.com',
                personal: false,
                disposable: false,
                valid: true,
            );

            expect($email->personal)->toBeFalse()
                ->and($email->disposable)->toBeFalse()
                ->and($email->valid)->toBeTrue();
        });

        it('correctly identifies disposable email service', function (): void {
            $email = new Email(
                email: 'quicktest@10minutemail.com',
                personal: true,
                disposable: true,
                valid: true,
            );

            expect($email->personal)->toBeTrue()
                ->and($email->disposable)->toBeTrue()
                ->and($email->valid)->toBeTrue();
        });

        it('handles mixed scenarios correctly', function (): void {
            // Business email that's disposable (unusual but possible)
            $businessDisposable = new Email(
                email: 'business@tempmail.org',
                personal: false,
                disposable: true,
                valid: true,
            );

            expect($businessDisposable->personal)->toBeFalse()
                ->and($businessDisposable->disposable)->toBeTrue()
                ->and($businessDisposable->valid)->toBeTrue();
        });
    });
});

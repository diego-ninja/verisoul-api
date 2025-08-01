<?php

use Ninja\Verisoul\Enums\VerificationStatus;

describe('VerificationStatus Enum', function (): void {
    describe('enum cases', function (): void {
        it('has all expected cases', function (): void {
            $cases = VerificationStatus::cases();
            $values = array_map(fn($case) => $case->value, $cases);

            expect($cases)->toHaveCount(5)
                ->and($values)->toContain('pending')
                ->and($values)->toContain('verified')
                ->and($values)->toContain('failed')
                ->and($values)->toContain('expired')
                ->and($values)->toContain('manual_review');
        });

        it('has correct enum values', function (): void {
            expect(VerificationStatus::Pending->value)->toBe('pending')
                ->and(VerificationStatus::Verified->value)->toBe('verified')
                ->and(VerificationStatus::Failed->value)->toBe('failed')
                ->and(VerificationStatus::Expired->value)->toBe('expired')
                ->and(VerificationStatus::ManualReview->value)->toBe('manual_review');
        });

        it('can be created from string values', function (): void {
            expect(VerificationStatus::from('pending'))->toBe(VerificationStatus::Pending)
                ->and(VerificationStatus::from('verified'))->toBe(VerificationStatus::Verified)
                ->and(VerificationStatus::from('failed'))->toBe(VerificationStatus::Failed)
                ->and(VerificationStatus::from('expired'))->toBe(VerificationStatus::Expired)
                ->and(VerificationStatus::from('manual_review'))->toBe(VerificationStatus::ManualReview);
        });

        it('can try to create from string values', function (): void {
            expect(VerificationStatus::tryFrom('pending'))->toBe(VerificationStatus::Pending)
                ->and(VerificationStatus::tryFrom('invalid'))->toBeNull();
        });
    });

    describe('values method', function (): void {
        it('returns all enum values as array', function (): void {
            $values = VerificationStatus::values();

            expect($values)->toBeArray()
                ->and($values)->toHaveCount(5)
                ->and($values)->toContain('pending')
                ->and($values)->toContain('verified')
                ->and($values)->toContain('failed')
                ->and($values)->toContain('expired')
                ->and($values)->toContain('manual_review');
        });

        it('maintains correct order', function (): void {
            $values = VerificationStatus::values();

            expect($values[0])->toBe('pending')
                ->and($values[1])->toBe('verified')
                ->and($values[2])->toBe('failed')
                ->and($values[3])->toBe('expired')
                ->and($values[4])->toBe('manual_review');
        });
    });

    describe('status checking methods', function (): void {
        it('isPending method works correctly', function (): void {
            expect(VerificationStatus::Pending->isPending())->toBeTrue()
                ->and(VerificationStatus::Verified->isPending())->toBeFalse()
                ->and(VerificationStatus::Failed->isPending())->toBeFalse()
                ->and(VerificationStatus::Expired->isPending())->toBeFalse()
                ->and(VerificationStatus::ManualReview->isPending())->toBeFalse();
        });

        it('isVerified method works correctly', function (): void {
            expect(VerificationStatus::Verified->isVerified())->toBeTrue()
                ->and(VerificationStatus::Pending->isVerified())->toBeFalse()
                ->and(VerificationStatus::Failed->isVerified())->toBeFalse()
                ->and(VerificationStatus::Expired->isVerified())->toBeFalse()
                ->and(VerificationStatus::ManualReview->isVerified())->toBeFalse();
        });

        it('isFailed method works correctly', function (): void {
            expect(VerificationStatus::Failed->isFailed())->toBeTrue()
                ->and(VerificationStatus::Pending->isFailed())->toBeFalse()
                ->and(VerificationStatus::Verified->isFailed())->toBeFalse()
                ->and(VerificationStatus::Expired->isFailed())->toBeFalse()
                ->and(VerificationStatus::ManualReview->isFailed())->toBeFalse();
        });

        it('isExpired method works correctly', function (): void {
            expect(VerificationStatus::Expired->isExpired())->toBeTrue()
                ->and(VerificationStatus::Pending->isExpired())->toBeFalse()
                ->and(VerificationStatus::Verified->isExpired())->toBeFalse()
                ->and(VerificationStatus::Failed->isExpired())->toBeFalse()
                ->and(VerificationStatus::ManualReview->isExpired())->toBeFalse();
        });

        it('requiresManualReview method works correctly', function (): void {
            expect(VerificationStatus::ManualReview->requiresManualReview())->toBeTrue()
                ->and(VerificationStatus::Pending->requiresManualReview())->toBeFalse()
                ->and(VerificationStatus::Verified->requiresManualReview())->toBeFalse()
                ->and(VerificationStatus::Failed->requiresManualReview())->toBeFalse()
                ->and(VerificationStatus::Expired->requiresManualReview())->toBeFalse();
        });
    });

    describe('completion status', function (): void {
        it('isCompleted method works correctly', function (): void {
            expect(VerificationStatus::Verified->isCompleted())->toBeTrue()
                ->and(VerificationStatus::Failed->isCompleted())->toBeTrue()
                ->and(VerificationStatus::Expired->isCompleted())->toBeTrue()
                ->and(VerificationStatus::Pending->isCompleted())->toBeFalse()
                ->and(VerificationStatus::ManualReview->isCompleted())->toBeFalse();
        });

        it('identifies all completed statuses', function (): void {
            $completedStatuses = [
                VerificationStatus::Verified,
                VerificationStatus::Failed,
                VerificationStatus::Expired,
            ];

            foreach ($completedStatuses as $status) {
                expect($status->isCompleted())->toBeTrue();
            }
        });

        it('identifies all non-completed statuses', function (): void {
            $nonCompletedStatuses = [
                VerificationStatus::Pending,
                VerificationStatus::ManualReview,
            ];

            foreach ($nonCompletedStatuses as $status) {
                expect($status->isCompleted())->toBeFalse();
            }
        });
    });

    describe('state transitions', function (): void {
        it('allows valid transitions from Pending', function (): void {
            $pending = VerificationStatus::Pending;

            expect($pending->canTransitionTo(VerificationStatus::Verified))->toBeTrue()
                ->and($pending->canTransitionTo(VerificationStatus::Failed))->toBeTrue()
                ->and($pending->canTransitionTo(VerificationStatus::ManualReview))->toBeTrue()
                ->and($pending->canTransitionTo(VerificationStatus::Expired))->toBeFalse()
                ->and($pending->canTransitionTo(VerificationStatus::Pending))->toBeFalse();
        });

        it('allows valid transitions from ManualReview', function (): void {
            $manualReview = VerificationStatus::ManualReview;

            expect($manualReview->canTransitionTo(VerificationStatus::Verified))->toBeTrue()
                ->and($manualReview->canTransitionTo(VerificationStatus::Failed))->toBeTrue()
                ->and($manualReview->canTransitionTo(VerificationStatus::Pending))->toBeFalse()
                ->and($manualReview->canTransitionTo(VerificationStatus::Expired))->toBeFalse()
                ->and($manualReview->canTransitionTo(VerificationStatus::ManualReview))->toBeFalse();
        });

        it('allows valid transitions from Verified', function (): void {
            $verified = VerificationStatus::Verified;

            expect($verified->canTransitionTo(VerificationStatus::Expired))->toBeTrue()
                ->and($verified->canTransitionTo(VerificationStatus::Pending))->toBeFalse()
                ->and($verified->canTransitionTo(VerificationStatus::Failed))->toBeFalse()
                ->and($verified->canTransitionTo(VerificationStatus::ManualReview))->toBeFalse()
                ->and($verified->canTransitionTo(VerificationStatus::Verified))->toBeFalse();
        });

        it('disallows transitions from terminal states', function (): void {
            $terminalStates = [VerificationStatus::Failed, VerificationStatus::Expired];

            foreach ($terminalStates as $terminalState) {
                $allStatuses = VerificationStatus::cases();

                foreach ($allStatuses as $targetStatus) {
                    expect($terminalState->canTransitionTo($targetStatus))->toBeFalse();
                }
            }
        });

        it('validates complete state machine', function (): void {
            $validTransitions = [
                [VerificationStatus::Pending, [
                    VerificationStatus::Verified,
                    VerificationStatus::Failed,
                    VerificationStatus::ManualReview,
                ]],
                [VerificationStatus::ManualReview, [
                    VerificationStatus::Verified,
                    VerificationStatus::Failed,
                ]],
                [VerificationStatus::Verified, [
                    VerificationStatus::Expired,
                ]],
                [VerificationStatus::Failed, []],
                [VerificationStatus::Expired, []],
            ];

            foreach ($validTransitions as [$fromStatus, $allowedTargets]) {
                $allStatuses = VerificationStatus::cases();

                foreach ($allStatuses as $targetStatus) {
                    $shouldAllow = in_array($targetStatus, $allowedTargets, true);
                    expect($fromStatus->canTransitionTo($targetStatus))->toBe($shouldAllow);
                }
            }
        });
    });

    describe('workflow patterns', function (): void {
        it('supports basic verification workflow', function (): void {
            // Start with pending
            $status = VerificationStatus::Pending;
            expect($status->isPending())->toBeTrue();

            // Can go to verified
            expect($status->canTransitionTo(VerificationStatus::Verified))->toBeTrue();

            // Verified can expire
            expect(VerificationStatus::Verified->canTransitionTo(VerificationStatus::Expired))->toBeTrue();
        });

        it('supports manual review workflow', function (): void {
            // Start with pending
            $status = VerificationStatus::Pending;

            // Can go to manual review
            expect($status->canTransitionTo(VerificationStatus::ManualReview))->toBeTrue();

            // Manual review can resolve to verified or failed
            expect(VerificationStatus::ManualReview->canTransitionTo(VerificationStatus::Verified))->toBeTrue();
            expect(VerificationStatus::ManualReview->canTransitionTo(VerificationStatus::Failed))->toBeTrue();
        });

        it('supports failure workflow', function (): void {
            // Can fail from pending
            expect(VerificationStatus::Pending->canTransitionTo(VerificationStatus::Failed))->toBeTrue();

            // Can fail from manual review
            expect(VerificationStatus::ManualReview->canTransitionTo(VerificationStatus::Failed))->toBeTrue();

            // Failed is terminal
            expect(VerificationStatus::Failed->isCompleted())->toBeTrue();
        });
    });

    describe('enum behavior', function (): void {
        it('supports comparison operations', function (): void {
            expect(VerificationStatus::Pending === VerificationStatus::Pending)->toBeTrue()
                ->and(VerificationStatus::Pending === VerificationStatus::Verified)->toBeFalse()
                ->and(VerificationStatus::Pending !== VerificationStatus::Verified)->toBeTrue();
        });

        it('can be used in match expressions', function (): void {
            $status = VerificationStatus::ManualReview;

            $message = match ($status) {
                VerificationStatus::Pending => 'Waiting for verification',
                VerificationStatus::Verified => 'Successfully verified',
                VerificationStatus::Failed => 'Verification failed',
                VerificationStatus::Expired => 'Verification expired',
                VerificationStatus::ManualReview => 'Requires manual review',
            };

            expect($message)->toBe('Requires manual review');
        });

        it('can be used in arrays and collections', function (): void {
            $activeStatuses = [
                VerificationStatus::Pending,
                VerificationStatus::ManualReview,
            ];

            expect($activeStatuses)->toHaveCount(2)
                ->and(in_array(VerificationStatus::Pending, $activeStatuses))->toBeTrue()
                ->and(in_array(VerificationStatus::Verified, $activeStatuses))->toBeFalse();
        });

        it('supports serialization', function (): void {
            $status = VerificationStatus::ManualReview;
            $serialized = serialize($status);
            $unserialized = unserialize($serialized);

            expect($unserialized)->toBe(VerificationStatus::ManualReview)
                ->and($unserialized->value)->toBe('manual_review')
                ->and($unserialized->requiresManualReview())->toBeTrue();
        });
    });

    describe('validation and error handling', function (): void {
        it('throws exception for invalid string values', function (): void {
            expect(fn() => VerificationStatus::from('invalid'))
                ->toThrow(ValueError::class);
        });

        it('handles case sensitivity correctly', function (): void {
            expect(VerificationStatus::tryFrom('PENDING'))->toBeNull()
                ->and(VerificationStatus::tryFrom('Pending'))->toBeNull()
                ->and(VerificationStatus::tryFrom('VERIFIED'))->toBeNull()
                ->and(VerificationStatus::tryFrom('Verified'))->toBeNull();
        });

    });

    describe('business logic helpers', function (): void {
        it('identifies statuses that need action', function (): void {
            $needsAction = [
                VerificationStatus::Pending,
                VerificationStatus::ManualReview,
            ];

            foreach ($needsAction as $status) {
                expect($status->isCompleted())->toBeFalse();
            }
        });

        it('identifies final statuses', function (): void {
            $finalStatuses = [
                VerificationStatus::Verified,
                VerificationStatus::Failed,
                VerificationStatus::Expired,
            ];

            foreach ($finalStatuses as $status) {
                expect($status->isCompleted())->toBeTrue();
            }
        });

        it('supports status filtering by category', function (): void {
            $allStatuses = VerificationStatus::cases();

            $completedStatuses = array_filter($allStatuses, fn($status) => $status->isCompleted());
            $pendingStatuses = array_filter($allStatuses, fn($status) => ! $status->isCompleted());

            expect($completedStatuses)->toHaveCount(3)
                ->and($pendingStatuses)->toHaveCount(2);
        });
    });

    describe('string representation', function (): void {
        it('converts to string correctly', function (): void {
            expect(VerificationStatus::Pending->value)->toBe('pending')
                ->and(VerificationStatus::Verified->value)->toBe('verified')
                ->and(VerificationStatus::Failed->value)->toBe('failed')
                ->and(VerificationStatus::Expired->value)->toBe('expired')
                ->and(VerificationStatus::ManualReview->value)->toBe('manual_review');
        });

        it('provides meaningful string representation', function (): void {
            expect((string) VerificationStatus::ManualReview->value)->toBe('manual_review');
        });
    });
});

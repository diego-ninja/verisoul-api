<?php

namespace Ninja\Verisoul\Enums;

enum VerificationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Failed = 'failed';
    case Expired = 'expired';
    case ManualReview = 'manual_review';

    public static function values(): array
    {
        return [
            self::Pending->value,
            self::Verified->value,
            self::Failed->value,
            self::Expired->value,
            self::ManualReview->value,
        ];
    }

    public function isPending(): bool
    {
        return self::Pending === $this;
    }

    public function isVerified(): bool
    {
        return self::Verified === $this;
    }

    public function isFailed(): bool
    {
        return self::Failed === $this;
    }

    public function isExpired(): bool
    {
        return self::Expired === $this;
    }

    public function requiresManualReview(): bool
    {
        return self::ManualReview === $this;
    }

    public function isCompleted(): bool
    {
        return in_array($this, [self::Verified, self::Failed, self::Expired], true);
    }

    public function canTransitionTo(self $status): bool
    {
        return match ($this) {
            self::Pending => in_array($status, [self::Verified, self::Failed, self::ManualReview], true),
            self::ManualReview => in_array($status, [self::Verified, self::Failed], true),
            self::Verified => self::Expired === $status,
            default => false,
        };
    }
}

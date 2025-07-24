<?php

namespace Ninja\Verisoul\Enums;

enum LivenessSession: string
{
    case FaceMatch = 'face-match';
    case IDCheck = 'id-check';

    public function getVerificationType(): VerificationType
    {
        return match ($this) {
            self::FaceMatch => VerificationType::Face,
            self::IDCheck => VerificationType::Identity,
        };
    }
}

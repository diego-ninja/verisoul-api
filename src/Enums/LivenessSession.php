<?php

namespace Ninja\Verisoul\Enums;

enum LivenessSession: string
{
    case FaceMatch = 'facematch';
    case IDCheck = 'id-check';

    public function getVerificationType(): VerificationType
    {
        return match ($this) {
            self::FaceMatch => VerificationType::Face,
            self::IDCheck => VerificationType::Identity,
        };
    }
}

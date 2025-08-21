<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\Granite;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;
use Ninja\Verisoul\Enums\IDBarcodeStatus;
use Ninja\Verisoul\Enums\IDDigitalSpoof;
use Ninja\Verisoul\Enums\IDFaceStatus;
use Ninja\Verisoul\Enums\IDStatus;
use Ninja\Verisoul\Enums\IDTextStatus;
use Ninja\Verisoul\Enums\IDValidity;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class DocumentSignals extends Granite
{
    public function __construct(
        public int $idAge,
        public float $idFaceMatchScore,
        public IDBarcodeStatus $idBarcodeStatus,
        public IDFaceStatus $idFaceStatus,
        public IDTextStatus $idTextStatus,
        public IDDigitalSpoof $isIdDigitalSpoof,
        public IDStatus $isFullIdCaptured,
        public IDValidity $idValidity,
    ) {}
}

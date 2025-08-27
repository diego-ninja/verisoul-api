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
use Ninja\Verisoul\Support\EnumLogger;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class DocumentSignals extends Granite
{
    public function __construct(
        public int $idAge,
        public float $idFaceMatchScore,
        public IDBarcodeStatus $idBarcodeStatus = IDBarcodeStatus::Unknown,
        public IDFaceStatus $idFaceStatus = IDFaceStatus::Unknown,
        public IDTextStatus $idTextStatus = IDTextStatus::Unknown,
        public IDDigitalSpoof $isIdDigitalSpoof = IDDigitalSpoof::Unknown,
        public IDStatus $isFullIdCaptured = IDStatus::Unknown,
        public IDValidity $idValidity = IDValidity::Unknown,
    ) {}

    protected static function rules(): array
    {
        return [
            'idBarcodeStatus' => [EnumLogger::logOnFail(self::class, 'idBarcodeStatus')],
            'idFaceStatus' => [EnumLogger::logOnFail(self::class, 'idFaceStatus')],
            'idTextStatus' => [EnumLogger::logOnFail(self::class, 'idTextStatus')],
            'isIdDigitalSpoof' => [EnumLogger::logOnFail(self::class, 'isIdDigitalSpoof')],
            'isFullIdCaptured' => [EnumLogger::logOnFail(self::class, 'isFullIdCaptured')],
            'idValidity' => [EnumLogger::logOnFail(self::class, 'idValidity')],


        ];
    }
}

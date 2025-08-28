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
            'id_barcode_status' => [EnumLogger::logOnFail(IDBarcodeStatus::class, 'idBarcodeStatus')],
            'id_face_status' => [EnumLogger::logOnFail(IDFaceStatus::class, 'idFaceStatus')],
            'id_text_status' => [EnumLogger::logOnFail(IDTextStatus::class, 'idTextStatus')],
            'is_id_digital_spoof' => [EnumLogger::logOnFail(IDDigitalSpoof::class, 'isIdDigitalSpoof')],
            'is_full_id_captured' => [EnumLogger::logOnFail(IDStatus::class, 'isFullIdCaptured')],
            'id_validity' => [EnumLogger::logOnFail(IDValidity::class, 'idValidity')],


        ];
    }
}

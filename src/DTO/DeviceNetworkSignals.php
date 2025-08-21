<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\Granite;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class DeviceNetworkSignals extends Granite
{
    public function __construct(
        public float $deviceRisk,
        public float $proxy,
        public float $vpn,
        public float $datacenter,
        public float $tor,
        public float $spoofedIp,
        public float $recentFraudIp,
        public float $deviceNetworkMismatch,
        public float $locationSpoofing,
    ) {}
}

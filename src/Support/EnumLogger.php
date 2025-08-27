<?php

namespace Ninja\Verisoul\Support;

use BackedEnum;
use Ninja\Granite\Validation\Rules\Callback;
use ReflectionEnum;

final class EnumLogger
{
    public static function logOnFail(string $enumClass, string $property): Callback
    {
        return new Callback(function (string|int $value) use ($enumClass, $property): bool {
            if ( ! enum_exists($enumClass)) {
                return true;
            }
            $reflection = new ReflectionEnum($enumClass);
            if ( ! $reflection->isBacked()) {
                return true;
            }

            if ($reflection->isBacked()) {
                /** @var class-string<BackedEnum> $enumClass */
                if (null === $enumClass::tryFrom($value)) {
                    $logValue = is_string($value) ? $value : var_export($value, true);
                    Logger::getInstance()->warning(sprintf(
                        'Unexpected value on deserialization in %s::%s => %s',
                        $enumClass,
                        $property,
                        $logValue,
                    ), [
                        'class' => $enumClass,
                        'property' => $property,
                        'value' => $logValue,
                    ]);
                }
            }

            return true;
        });
    }
}

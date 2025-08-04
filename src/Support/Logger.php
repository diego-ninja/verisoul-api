<?php

namespace Ninja\Verisoul\Support;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class Logger
{
    private static ?LoggerInterface $instance = null;

    public static function setLogger(LoggerInterface $logger): void
    {
        self::$instance = $logger;
    }

    public static function getInstance(): LoggerInterface
    {
        if (null === self::$instance) {
            self::$instance = new NullLogger();
        }

        return self::$instance;
    }

    public static function info(string $message, array $context = []): void
    {
        self::getInstance()->info($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::getInstance()->error($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::getInstance()->warning($message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::getInstance()->debug($message, $context);
    }
}

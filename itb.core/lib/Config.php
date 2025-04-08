<?php
namespace Itb\Core;

/**
 * Конфиг для работы с переменными на сайте из .env
 */
final class Config
{
    private static ?string $mode = null;
    private static ?string $viteBasePath = null;
    private static ?string $viteClientPath = null;
    private static ?string $vitePort = null;
    private static ?bool $isEnableSsr = null;
    private static ?string $viteSsrPort = null;
    private static ?string $viteSsrHost = null;
    public static string $pathToPublicImages = "/local/js/vite/public/images";

    private function __construct() {}

    private static function loadEnvVar(mixed &$property, string $envName, mixed $default = null): mixed
    {
        if ($property === null) {
            $property = getEnvVar($envName, $default);
        }
        return $property;
    }

    public static function getMode(): string
    {
        return self::loadEnvVar(self::$mode, 'MODE', 'production');
    }

    public static function isProduction(): bool
    {
        return self::getMode() === 'production';
    }

    public static function getViteBasePath(): string
    {
        return self::loadEnvVar(self::$viteBasePath, 'VITE_BASE_PATH');
    }

    public static function getViteClientPath(): string
    {
        return self::loadEnvVar(self::$viteClientPath, 'VITE_CLIENT_PATH');
    }
    
    public static function getVitePort(): string
    {
        return self::loadEnvVar(self::$vitePort, 'VITE_PORT');
    }
    
    public static function isEnableViteSsr(): bool
    {
        return self::loadEnvVar(self::$isEnableSsr, 'VITE_SSR_ENABLE', false);
    }
    
    public static function getViteSsrPort(): string
    {
        return self::loadEnvVar(self::$viteSsrPort, 'VITE_SSR_PORT');
    }

    public static function getViteSsrHost(): string
    {
        return self::loadEnvVar(self::$viteSsrHost, 'VITE_SSR_HOST');
    }
}

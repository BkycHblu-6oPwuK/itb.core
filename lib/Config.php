<?php

namespace Itb\Core;

final class Config
{
    private static ?self $instance = null;

    public readonly string $mode;
    public readonly string $viteBasePath;
    public readonly string $viteClientPath;
    public readonly string $vitePort;
    public readonly bool $isEnableSsr;
    public readonly string $viteSsrPort;
    public readonly string $viteSsrHost;
    public readonly string $dateFormatSite;
    public readonly string $actionLoadItems;
    public readonly bool $enableSwitchCatalogType;

    public readonly string $pathToPublicImages;

    private function __construct()
    {
        $this->mode = $_ENV['MODE'] ?? 'production';
        $this->viteBasePath = $_ENV['VITE_BASE_PATH'] ?? '';
        $this->viteClientPath = $_ENV['VITE_CLIENT_PATH'] ?? '';
        $this->vitePort = $_ENV['VITE_PORT'] ?? '';
        $this->isEnableSsr = $_ENV['VITE_SSR_ENABLE'] == 1;
        $this->viteSsrPort = $_ENV['VITE_SSR_PORT'] ?? '';
        $this->viteSsrHost = $_ENV['VITE_SSR_HOST'] ?? '';
        $this->pathToPublicImages = "/{$this->viteBasePath}/public";
        $this->dateFormatSite = 'd.m.Y';
        $this->actionLoadItems = 'loadItems';
        $this->enableSwitchCatalogType = $_ENV['SWITH_CATALOG_TYPES'] == 1;
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function isProduction(): bool
    {
        return $this->mode === 'production';
    }

    public function getImagePublicPath(string $path)
    {
        return $this->pathToPublicImages . $path;
    }
}

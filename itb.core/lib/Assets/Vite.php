<?php

namespace Itb\Core\Assets;

use Bitrix\Main\Page\Asset;
use Itb\Core\Config;

/**
 * Класс для подключения js и css из vite, а так же получения html с ssr сервера
 */
final class Vite
{
    private static $instance;
    private $manifestPath;
    private $basePath;
    private $manifest;
    private $clientDirectoryPath = '';
    private $localhostBasePath = '';
    private $viteClientIsIncluded = false;

    private function __construct()
    {
        $clientDirectoryPath = Config::getViteClientPath();
        if ($clientDirectoryPath) $this->clientDirectoryPath = $clientDirectoryPath . '/';
        $this->basePath = '/' . Config::getViteBasePath() . '/';
        $this->manifestPath = "{$_SERVER['DOCUMENT_ROOT']}{$this->basePath}{$this->clientDirectoryPath}.vite/manifest.json";

        if (Config::isProduction()) {
            $this->loadManifest();
        } else {
            $vitePort = Config::getVitePort();
            $this->localhostBasePath = "http://localhost:{$vitePort}{$this->basePath}";
        }
    }

    private function __clone() {}
    private function __wakeup() {}

    /**
     * Возвращает объект класса
     * 
     * @return static
     */
    public final static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Загружает manifest.json
     *
     * @return void
     */
    private function loadManifest(): void
    {
        if (!file_exists($this->manifestPath)) {
            throw new \Exception("Manifest file not found: " . $this->manifestPath);
        }

        $this->manifest = json_decode(file_get_contents($this->manifestPath), true);
        if ($this->manifest === null) {
            throw new \Exception("Failed to decode manifest file: " . json_last_error_msg());
        }
    }

    /**
     * Рекурсивно обходит импорты и заносит пути до css файлов в массив
     *
     * @param string $entry
     * @param array $cssFiles
     * @return void
     */
    protected function collectCssImports($entry, &$cssFiles): void
    {
        if (isset($this->manifest[$entry])) {
            $asset = $this->manifest[$entry];
            if (isset($asset['css'])) {
                foreach ($asset['css'] as $cssFile) {
                    $cssFiles[] = $this->basePath . $this->clientDirectoryPath . $cssFile;
                }
            }
            if (isset($asset['imports'])) {
                foreach ($asset['imports'] as $import) {
                    $this->collectCssImports($import, $cssFiles);
                }
            }
        }
    }

    /**
     * Получает пути до js и css файлов. Для prod среды js и css. Для dev только js, css импортируем в js
     *
     * @param array $entries
     * @return array
     */
    protected function getAssetPaths(array $entries): array
    {
        $assets = [
            'js' => [],
            'css' => []
        ];

        if (Config::isProduction()) {
            foreach ($entries as $entry) {
                if (isset($this->manifest[$entry])) {
                    $asset = $this->manifest[$entry];
                    $assets['js'][] = [
                        'file' => $this->basePath . $this->clientDirectoryPath . $asset['file'],
                        'issetImports' => isset($asset['imports'])
                    ];
                    $this->collectCssImports($entry, $assets['css']);
                }
            }
        } else {
            if (!$this->viteClientIsIncluded) {
                $assets['js'][]['file'] = $this->localhostBasePath . '@vite/client';
                $this->viteClientIsIncluded = true;
            }
            foreach ($entries as $entry) {
                $assets['js'][]['file'] = $this->localhostBasePath . $entry;
            }
        }
        return $assets;
    }

    /**
     * Подключает js через Asset::addJs, js type module через Asset::addString если есть импорты, и css через Asset::addCss. Для prod среды js и css. Для dev только js, css импортируем в js.
     * Вызывать данный метод нужно в некешируемой области
     * @param string[] $entries относительно директории в которой расположен vite
     * @return void
     */
    public function includeAssets(array $entries): void
    {
        $assets = $this->getAssetPaths($entries);
        $bitrixAssetObj = Asset::getInstance();
        foreach ($assets['js'] as $jsInfo) {
            $jsFile = htmlspecialchars($jsInfo['file'], ENT_QUOTES);
            $bitrixAssetObj->addString("<script type='module' src='{$jsFile}'></script>", true);
        }
        if (Config::isProduction() && !empty($assets['css'])) {
            foreach ($assets['css'] as $cssFile) {
                $bitrixAssetObj->addCss(htmlspecialchars($cssFile, ENT_QUOTES), true);
            }
        }
    }

    /**
     * Подключает js и css с помощью addExternalJs и addExternalCss класса CBitrixComponentTemplate, js type module если есть импорты.
     *
     * @param string[] $entries относительно директории в которой расположен vite
     * @param CBitrixComponentTemplate $template в template.php можно просто передать $this
     * 
     * @return void
     * @deprecated Используйте includeAssets в component_epilog
     */
    public function includeExternalAssets(array $entries, \CBitrixComponentTemplate $template)
    {
        return;
        $assets = $this->getAssetPaths($entries);
        foreach ($assets['js'] as $jsInfo) {
            $jsFile = htmlspecialchars($jsInfo['file'], ENT_QUOTES);
            if ($jsInfo['issetImports'] || !Config::isProduction()) {
                echo "<script type='module' src='{$jsFile}'></script>";
            } else {
                $template->addExternalJs($jsFile);
            }
        }
        if (Config::isProduction() && !empty($assets['css'])) {
            foreach ($assets['css'] as $cssFile) {
                $template->addExternalCss(htmlspecialchars($cssFile, ENT_QUOTES));
            }
        }
    }
}

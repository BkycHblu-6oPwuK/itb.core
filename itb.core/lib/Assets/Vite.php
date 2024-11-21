<?php

namespace Itb\Core\Assets;

use Bitrix\Main\Page\Asset;

/**
 * Класс для подключения js и css из vite, а так же получения html с ssr сервера
 */
class Vite
{
    private static $instance;
    private $manifestPath;
    private $basePath;
    private $manifest;
    private $clientDirectoryPath = '';
    protected static $isProduction;
    protected static $ssrEnable;
    protected static $ssrPort;
    protected static $ssrHost;
    protected $localhostBasePath = '';
    protected $viteClientIsIncluded = false;

    private function __construct()
    {
        $clientDirectoryPath = getEnvVar('VITE_CLIENT_PATH');
        if ($clientDirectoryPath) $this->clientDirectoryPath = $clientDirectoryPath . '/';
        $this->basePath = '/' . getEnvVar('VITE_BASE_PATH') . '/';
        $this->manifestPath = "{$_SERVER['DOCUMENT_ROOT']}{$this->basePath}{$this->clientDirectoryPath}.vite/manifest.json";

        if (self::isProduction()) {
            $this->loadManifest();
        } else {
            $vitePort = getEnvVar('VITE_PORT');
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
        $manifest = $this->getManifest();
        if (isset($manifest[$entry])) {
            $basePath = $this->getBasePath();
            $clientDirectoryPath = $this->getClientDirectoryPath();
            $asset = $manifest[$entry];
            if (isset($asset['css'])) {
                foreach ($asset['css'] as $cssFile) {
                    $cssFiles[] = $basePath . $clientDirectoryPath . $cssFile;
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

        if (self::isProduction()) {
            $manifest = $this->getManifest();
            $basePath = $this->getBasePath();
            $clientDirectoryPath = $this->getClientDirectoryPath();
            foreach ($entries as $entry) {
                if (isset($manifest[$entry])) {
                    $asset = $manifest[$entry];
                    $assets['js'][] = [
                        'file' => $basePath . $clientDirectoryPath . $asset['file'],
                        'issetImports' => isset($asset['imports'])
                    ];
                    $this->collectCssImports($entry, $assets['css']);
                }
            }
        } else {
            $localhostBasePath = $this->getLocalhostBasePath();
            if (!$this->viteClientIsIncluded) {
                $assets['js'][]['file'] = $localhostBasePath . '@vite/client';
                $this->viteClientIsIncluded = true;
            }
            foreach ($entries as $entry) {
                $assets['js'][]['file'] = $localhostBasePath . $entry;
            }
        }
        return $assets;
    }

    /**
     * Подключает js через Asset::addJs, js type module через Asset::addString если есть импорты, и css через Asset::addCss. Для prod среды js и css. Для dev только js, css импортируем в js
     *
     * @param string[] $entries относительно директории в которой расположен vite
     * @return void
     */
    public function includeAssets(array $entries): void
    {
        $assets = $this->getAssetPaths($entries);
        $bitrixAssetObj = Asset::getInstance();
        foreach ($assets['js'] as $jsInfo) {
            $jsFile = htmlspecialchars($jsInfo['file'], ENT_QUOTES);
            if ($jsInfo['issetImports'] || !self::isProduction()) {
                $bitrixAssetObj->addString("<script type='module' src='{$jsFile}'></script>");
            } else {
                $bitrixAssetObj->addJs($jsFile);
            }
        }
        if (self::isProduction() && !empty($assets['css'])) {
            foreach ($assets['css'] as $cssFile) {
                $bitrixAssetObj->addCss(htmlspecialchars($cssFile, ENT_QUOTES));
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
            if ($jsInfo['issetImports'] || !self::isProduction()) {
                echo "<script type='module' src='{$jsFile}'></script>";
            } else {
                $template->addExternalJs($jsFile);
            }
        }
        if (self::isProduction() && !empty($assets['css'])) {
            foreach ($assets['css'] as $cssFile) {
                $template->addExternalCss(htmlspecialchars($cssFile, ENT_QUOTES));
            }
        }
    }

    /**
     * Вернет массив с манифестом vite клиента
     */
    final protected function getManifest(): array
    {
        return $this->manifest;
    }

    /**
     * Базовый путь до директории vite
     */
    protected function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Базовый путь до директории с клиентскими ассетами относительно базовой директории
     */
    protected function getClientDirectoryPath(): string
    {
        return $this->clientDirectoryPath;
    }

    /**
     * Базовый адрес сервера разработки
     */
    protected function getLocalhostBasePath(): string
    {
        return $this->localhostBasePath;
    }

    /**
     * получает html переданной страницы с сервера node для ssr, VITE_SSR_ENABLE должно быть установлено в значение 1
     * @throws InvalidArgumentException если не объявлены VITE_SSR_HOST, VITE_SSR_PORT и MODE 
     */
    public static function getSsrContent(string $page, ?array $data = null): ?string
    {
        if (!self::ssrEnable() || !self::isProduction()) return null;
        $httpClient = new \Bitrix\Main\Web\HttpClient();
        $httpClient->setHeader('Content-Type', 'application/json', true);
        $response = $httpClient->post(self::getSsrServerUrl() . "/{$page}", $data ? \Bitrix\Main\Web\Json::encode(['data' => $data]) : $data);
        if ($response && $httpClient->getStatus() === 200) {
            return $response;
        } else {
            return null;
        }
    }

    /**
     * Возвращает url ssr сервера с протоколом http
     * @throws InvalidArgumentException если не объявлены VITE_SSR_HOST и VITE_SSR_PORT
     */
    public static function getSsrServerUrl(): string
    {
        static $ssrServerUrl = null;
        if ($ssrServerUrl === null) {
            $host = self::getSsrHost();
            $port = self::getSsrPort();
            $ssrServerUrl = "http://{$host}:{$port}";
        }
        return $ssrServerUrl;
    }

    /**
     * Доступен ли node js сервер для ssr
     * @throws InvalidArgumentException если не объявлены VITE_SSR_HOST и VITE_SSR_PORT
     */
    public static function ssrServerIsAvailable(): bool
    {
        $httpClient = new \Bitrix\Main\Web\HttpClient();
        return $httpClient->get(self::getSsrServerUrl()) !== false;
    }

    /**
     * Возвращает host для ssr из переменнной VITE_SSR_HOST
     * @throws InvalidArgumentException если переменная VITE_SSR_HOST не объявлена 
     */
    public static function getSsrHost(): string
    {
        if (self::$ssrHost === null) {
            self::$ssrHost = getEnvVar('VITE_SSR_HOST');
        }
        return self::$ssrHost;
    }

    /**
     * Возвращает port для ssr из переменнной VITE_SSR_PORT
     * @throws InvalidArgumentException если переменная VITE_SSR_PORT не объявлена
     */
    public static function getSsrPort(): string
    {
        if (self::$ssrPort === null) {
            self::$ssrPort = getEnvVar('VITE_SSR_PORT');
        }
        return self::$ssrPort;
    }

    /**
     * Включен ли ssr
     */
    public static function ssrEnable(): bool
    {
        if (self::$ssrEnable === null) {
            self::$ssrEnable = (bool)getEnvVar('VITE_SSR_ENABLE', false);
        }
        return self::$ssrEnable;
    }

    /**
     * Проверка на продакшен среду
     * @throws InvalidArgumentException если переменная MODE не объявлена
     */
    public static function isProduction(): bool
    {
        if (self::$isProduction === null) {
            self::$isProduction = getEnvVar('MODE') === 'production';
        }
        return self::$isProduction;
    }
}

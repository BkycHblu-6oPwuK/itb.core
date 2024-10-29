<?php

namespace Itb\Core\Assets;

use Bitrix\Main\Page\Asset;

/**
 * Класс для подключения js и css из vite, а так же получения html с ssr сервера
 */
class Vite
{
    private $manifestPath;
    private $basePath;
    private $manifest;
    private $isProduction = true;
    private $localhostBasePath;
    private $viteClientIsIncluded = false;
    private $clientDirectoryPath = '';
    private static $instance;

    private function __construct(){}
    private function __clone(){}
    private function __wakeup(){}

    /**
     * инициализация параметров необходимых для работы класса
     */
    private function initialize()
    {
        $clientDirectoryPath = getEnvVar('VITE_CLIENT_PATH');
        if($clientDirectoryPath) $this->clientDirectoryPath = $clientDirectoryPath . '/';
        $this->basePath = '/' . getEnvVar('VITE_BASE_PATH') . '/';
        $this->isProduction = self::isProduction();
        $this->manifestPath = "{$_SERVER['DOCUMENT_ROOT']}{$this->basePath}{$this->clientDirectoryPath}.vite/manifest.json";
    
        if ($this->isProduction) {
            $this->loadManifest();
        } else {
            $vitePort = getEnvVar('VITE_PORT');
            $this->localhostBasePath = "http://localhost:{$vitePort}{$this->basePath}";
        }
    }

    /**
     * Возвращает объект класса
     * @return static
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
            static::$instance->initialize();
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
     *
     * @return void
     */
    private function collectCssImports($entry, &$cssFiles): void
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
     *
     * @return array
     */
    protected function getAssetPaths(array $entries): array
    {
        $assets = [
            'js' => [],
            'css' => []
        ];

        if ($this->isProduction) {
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
            if(!$this->viteClientIsIncluded){
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
     * Подключает js через Asset::addJs, js type module через Asset::addString если есть импорты, и css через Asset::addCss. Для prod среды js и css. Для dev только js, css импортируем в js
     *
     * @param string[] $entries относительно директории в которой расположен vite
     *
     * @return void
     */
    public function includeAssets(array $entries): void
    {
        $assets = $this->getAssetPaths($entries);
        $bitrixAssetObj = Asset::getInstance();
        foreach ($assets['js'] as $jsInfo) {
            $jsFile = htmlspecialchars($jsInfo['file'], ENT_QUOTES);
            if($jsInfo['issetImports'] || !$this->isProduction){
                $bitrixAssetObj->addString("<script type='module' src='{$jsFile}'></script>");
            } else {
                $bitrixAssetObj->addJs($jsFile);
            }
        }
        if ($this->isProduction && !empty($assets['css'])) {
            foreach ($assets['css'] as $cssFile) {
                $bitrixAssetObj->addCss(htmlspecialchars($cssFile, ENT_QUOTES));
            }
        }
    }

    /**
     * получает html переданной страницы с сервера node для ssr, VITE_SSR_ENABLE должно быть установлено в значение 1
     * @throws InvalidArgumentException
     */
    public static function getSsrContent(string $page, ?array $data = null) : ?string
    {
        $ssrEnable = self::ssrEnable();
        if(!$ssrEnable) return null;
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
     * @throws InvalidArgumentException
     */
    public static function getSsrServerUrl() : string
    {
        static $ssrServerUrl = null;
        if($ssrServerUrl === null) {
            $ssrPort = getEnvVar('VITE_SSR_PORT');
            $host = getEnvVar('VITE_SSR_HOST');
            $ssrServerUrl = "http://{$host}:{$ssrPort}";
        }
        return $ssrServerUrl;
    }

    /**
     * Доступен ли node js сервер для ssr
     */
    public static function ssrServerIsAvailable() : bool
    {
        $httpClient = new \Bitrix\Main\Web\HttpClient();
        return $httpClient->get(self::getSsrServerUrl()) !== false;
    }

    /**
     * Включен ли ssr
     */
    public static function ssrEnable() : bool
    {
        return (bool)getEnvVar('VITE_SSR_ENABLE', false);
    }

    /**
     * Проверка на продакшен среду
     * @throws InvalidArgumentException если переменная MODE не объявлена
     */
    public static function isProduction() : bool
    {
        return getEnvVar('MODE') === 'production';
    }
}

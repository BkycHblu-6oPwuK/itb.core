Удобные классы которые подойдут для любого проекта

- документация по билдеру настроек модуля - lib/Modules/Options/README.md
- документация по ресурсам - lib/Http/Resources/README.md
- документация по Vite - lib/Assets/README.md

Классы Хэлперы

- DateHelper
- FilesHelper
- HlblockHelper
- IblockHelper
- LanguageHelper
- LocationHelper
- PaginationHelper

## Рекомендуемые пакеты composer для проектов
Все пакеты устанавливаются с помощью команды ```composer require```

- ```symfony/var-dumper``` - удобные дампы, функции dump() и dd()
- ```illuminate/collections``` - коллекции laravel
- ```vlucas/phpdotenv``` - загрузка переменных окружения из файла .env, подключать в init.php (```php \Dotenv\Dotenv::createUnsafeImmutable(__DIR__)->load() ```)
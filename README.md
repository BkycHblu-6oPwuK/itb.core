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
- WebHelper
- SsrHelper


Psr logger - простая реализация, интерфейсы psr лежат в модуле main bitrix

- Itb\Core\Logger\FileLogger

## подключение
в init.php после подключения autoload composer сделайте

```php
Bitrix\Main\Loader::includeModule('itb.core');
```


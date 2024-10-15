## Класс Vite

Для работы необходимо чтобы был настроен файл .env в - local/php/interface/include/.env

```bash
MODE = development|production # локально ставим development, на боевом через ftp устанавливаем production
VITE_BASE_PATH = /local/js/vite/dist/
VITE_PORT = 5173 # как у контейнера node
```

Получение объекта класса:
```php 
$vite = Vite::getInstance() 
```

Подключение ассетов:
```php 
$vite->includeAssets([
	'src/common/js/bundle.js',
]);
```

- ```Vite::includeAssets``` - принимает массив путей относительно корневой директории с package.json с vite

Более подробно - https://git.itb-dev.ru/ITB-dev/example_template
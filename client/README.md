# Интеграция SPA в приложение для Webasyst

## Файловая структура

Исходный код frontend части приложения находится в директории client, вся работа ведется там. Внутренняя файловая структура определяется фреймворком, на котором разрабатывается проект. В данном случае – стандартная структура Vue.js.

Основные папки:
- src – исходники компонентов, стилей и тд
- dist – скомпилированные файлы приложения

## Работа с кодом и сборка

Подробно описано тут:
https://github.com/1312inc/Cash-app-Webasyst/blob/webasyst2/client/devguide.md

## Интеграция фронта в лейауты из templates

После сборки, production файлы фронта (app.js, app.css) автоматически копируются из /client/dist в стандартные директории приложения для Webasyst – /css, /js. Оттуда они подключаются стандартными методами в шаблоны Webasyst.

Типовые этапы интеграции js приложения в шаблон:

1. Подключение стилей для приложения
2. Передача данных в приложение через глобальные переменные (при необходимости)
3. Добавление DOM контейнера на старницу
4. Подключение js файлов приложения

Пример внедрения SPA в HTML шаблон приложения для Webasyst:

```html
   <!DOCTYPE html>
   <html>
   <head>
       ...
 
       {$wa->css()}
       <!-- 1. Include App styles -->
       <link type="text/css" rel="stylesheet" href="{$wa_app_static_url}css/app.css">
      
       <!-- 2. App environment -->
       <script>
           window.appState = {ldelim}"lang":"{$wa->locale()}","baseUrl":"{$wa_app_url}","baseApiUrl":"{$wa_url}api.php","token":"{$token}"{rdelim};
       </script>
   </head>
   <body>
   <div id="wa">
       {$wa->header()}
 
       <!-- 3. App container -->
       <div id="app"></div>
 
       ...
      
       <!-- 4. Include App javascript file -->
       <script type="text/javascript" src="{$wa_app_static_url}js/app.js"></script>
   </body>
   </html>
 ```

---
> :warning: **NOTE**

client директория с исходниками фронта в коммерческой продакшн версии отсутствует, только в dev ветке.
Юзеры, которые делают свои приложения или приложения Open source, могут не исключать исходный код фронта из репозиториев.

---

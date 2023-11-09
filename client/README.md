# Интеграция SPA для Webasyst

## Работа с кодом и сборка

Описано тут: [devguide.md](devguide.md)

## Интеграция SPA в лейауты из templates

После сборки production-файлы SPA (`app.js`, `app.css`) автоматически копируются из `/client/dist/desktop` в стандартные директории приложения для Webasyst – wa-apps/APP_ID/css, wa-apps/APP_ID/js. Оттуда они подключаются стандартными методами в шаблоны Webasyst.

Пример внедрения SPA в HTML шаблон приложения для Webasyst:

```html
   <!DOCTYPE html>
   <html>
   <head>
       ...
 
       {$wa->css()}
       <!-- 1. Include App styles -->
       <link type="text/css" rel="stylesheet" href="{$wa_app_static_url}css/app.css">
        <!-- 2. Include main App js file -->
       <script type="module" crossorigin src="{$wa_app_static_url}js/app.js"></script>
       <!-- 3. App environment (see index.html) -->
       <script>
           window.appState = {ldelim}"lang":"{$wa->locale()}","baseUrl":"{$wa_app_url}","baseApiUrl":"{$wa_url}api.php","token":"{$token}"{rdelim};
       </script>
   </head>
   <body>
   <div id="wa">
       {$wa->header()}
 
       <!-- 4. App container -->
       <div id="app"></div>
 
       ...
      
   </body>
   </html>
 ```


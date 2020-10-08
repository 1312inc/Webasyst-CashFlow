# Cash Flow Dev Guide 

Работа над приложением (в том числе версия, интегрируемая в приложения Android и iOS) ведется в рамках единого репозитория 1312inc/Cash-app-Webasyst в ветке webasyst2.

- Исходный код в директории /client/   
- Разработка ведется с использованием Vue CLI

#### Основные входные файлы для разделения разработки на 2 режима:
- /client/src/desktop.js для основной web версии  
- /client/src/mobile.js для мобильной

## Режим разработки
Для запуска режима используется команда vue-cli-service serve.

#### Запуск сервера разработки для web версии
`npm run serve-desktop `

#### Для мобильной версии
`npm run serve-mobile`

Подробнее об vue-cli-service на https://cli.vuejs.org/guide/cli-service.html

## Production сборка
Для сборки используется команда vue-cli-service build.

#### Сборка web версии
Производится командой `npm run build-desktop`  
В результате в директорию /client/dist/desktop генерируются css и js файлы, которые затем автоматически копируются в директории /css и /js для использования в production версии Cash Flow 2, путем подключения из шаблона страницы стандартными методами. Деплой происходит в рамках репозитория.

#### Сборка mobile версии
Осуществляется командой `npm run build-mobile`  
В результате в директорию /client/dist/mobile генерируются css и js файлы приложения.

Доставка файлов в репозиторий 1312inc/Cash-app-Android осуществляется в автоматическом режиме с использованием Github Action https://github.com/marketplace/actions/copycat-action

Правило срабатывания следующее:
- Происходит действие push на исходном репозитории
- Если изменения затрагивают директорию /client/dist/mobile (новая версия файлов)
- Срабатывает механизм копирования директорий /client/dist/mobile/css и /client/dist/mobile/js в директорию /cashflow/src/main/assets/spa/ репозитория назначения.
- Формируется соответствующий commit


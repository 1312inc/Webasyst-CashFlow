# Webasyst-CashFlow Dev Guide 

#### Перменные окружения
Для работы в dev режиме нужен файл с переменными окружения `.env.development.local` со следующим содержанием:

```sh
VITE_APP_DEV_PROXY=http://localhost:8888/ # хост, на котором запущена установка Webasyst (e.g. https://domain.com/sndbx/)
VITE_APP_API_TOKEN=3ebf...97968b2 # API токен
```
> :warning: **NOTE** 
.local файлы игнорируются git, их нужно создавать самостоятельно для локального использования

## Production сборка
В проекте используется Vite. Для сборки в двух режимах (desktop и mobile) существует команда vite build (см. package.json)

`desktop` режим собирает web приложения\
`mobile` для гибридного мобильного
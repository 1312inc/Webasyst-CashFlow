import Vue from 'vue'
import VueI18n from 'vue-i18n'
import en from '@/locales/en_US.json'
import ru from '@/locales/ru_RU.json'

Vue.use(VueI18n)

const locale = window?.appState?.lang || 'en_US'

const i18n = new VueI18n({
  locale,
  fallbackLocale: 'en_US',
  messages: {
    en_US: en,
    ru_RU: ru
  }
})

export { locale, i18n }
